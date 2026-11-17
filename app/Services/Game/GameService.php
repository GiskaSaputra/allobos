<?php

namespace App\Services\Game;

use App\Enums\GameStatus;
use App\Models\BusinessField;
use App\Models\ShopDay;
use App\Models\ShopGame;
use App\Models\User;
use App\Services\Ai\AiException;
use App\Services\Ai\FeatureSchemas;
use App\Services\Ai\GeminiClient;
use App\Services\Readiness\ReadinessCalculator;
use Illuminate\Support\Facades\DB;

/** Mengatur tahap permainan: belanja, harga, buka, laporan (WB-02). Keadaan disimpan tiap akhir tahap (WB-09). */
class GameService
{
    public function __construct(
        private readonly GameEngine $engine,
        private readonly EventPicker $events,
        private readonly BossLines $lines,
        private readonly GeminiClient $ai,
        private readonly ReadinessCalculator $readiness,
    ) {}

    public function start(User $user): ShopGame
    {
        return DB::transaction(function () use ($user) {
            ShopGame::query()->where('user_id', $user->id)->where('status', GameStatus::Berjalan->value)->update(['status' => GameStatus::Ditinggalkan->value]);

            $slugs = BusinessField::query()->whereIn('id', $user->profile?->interests ?? [])->pluck('slug')->all();
            $kind = GameRules::kindFor($slugs);
            $game = ShopGame::query()->create([
                'user_id' => $user->id,
                'kind' => $kind,
                'seed' => random_int(1, 2_000_000_000),
                'status' => GameStatus::Berjalan,
                'day' => 1,
                'phase' => 'belanja',
                'state' => $this->engine->newState($kind),
            ]);
            $this->beginDay($game);

            return $game;
        });
    }

    public function buy(ShopGame $game, array $quantities): ShopGame
    {
        $this->expect($game, 'belanja');
        $state = $this->engine->buy($game->state, array_map('intval', $quantities));

        return $this->save($game, $state, 'harga');
    }

    public function price(ShopGame $game, array $prices): ShopGame
    {
        $this->expect($game, 'harga');
        $state = $this->engine->setPrices($game->state, array_map('intval', $prices));

        return $this->save($game, $state, 'buka');
    }

    public function run(ShopGame $game, string $promo, string $special): array
    {
        $this->expect($game, 'buka');
        $decisions = ['stock' => array_map(fn ($s) => $s['qty'], $game->state['stock']), 'prices' => $game->state['prices'], 'promo' => $promo, 'special' => $special];
        [$state, $result] = $this->engine->run($game->state, $game->day, $promo, $special, $game->seed);
        $result['bos_comment'] = $this->lines->evening($result, $this->engine->dailyTarget($state));
        $state['last_result'] = $result;

        ShopDay::query()->updateOrCreate(
            ['shop_game_id' => $game->id, 'day' => $game->day],
            ['decisions' => $decisions, 'event' => $game->state['event'], 'result' => $result],
        );
        $this->save($game, $state, 'laporan');

        return $result;
    }

    public function next(ShopGame $game): ShopGame
    {
        $this->expect($game, 'laporan');

        if ($game->day >= 7) {
            return $this->finish($game);
        }

        $state = $game->state;
        $state['last_result'] = null;
        $game->day++;
        $this->save($game, $state, 'belanja');
        $this->beginDay($game);

        return $game;
    }

    public function saveLessons(ShopGame $game): void
    {
        if ($game->status !== GameStatus::Selesai) {
            throw new GameException('Selesaikan tujuh hari dulu sebelum menyimpan pelajaran.');
        }
        if ($game->saved_at) {
            return;
        }
        $game->update(['saved_at' => now()]);
        $this->readiness->record($game->user, 'warung', 'Menamatkan Warung Bos 3D dengan nilai '.$game->score, route('game.report', $game));
    }

    /** Data yang dikirim ke peramban. Tidak memuat benih acak. */
    public function view(ShopGame $game): array
    {
        $state = $game->state;
        $rules = GameRules::KINDS[$game->kind];
        $theme = GameRules::THEMES[$game->day];
        $special = GameRules::SPECIALS[$game->day];

        return [
            'id' => $game->id,
            'day' => $game->day,
            'phase' => $game->phase,
            'status' => $game->status->value,
            'kind' => $game->kind,
            'kind_label' => $rules['label'],
            'theme' => ['title' => $theme['title'], 'lesson' => $theme['lesson']],
            'flags' => ['rain' => $theme['rain'] ?? false, 'bus' => $theme['bus'] ?? false, 'competitor' => $game->day >= 3],
            'event' => $state['event'] ? ['title' => $state['event']['title'], 'story' => $state['event']['story']] : null,
            'bos_line' => BossLines::MORNING[$game->day],
            'cash' => $state['cash'],
            'start_cash' => $state['start_cash'],
            'satisfaction' => $state['satisfaction'],
            'target' => $this->engine->dailyTarget($state),
            'items' => collect($rules['items'])->map(fn ($item, $key) => [
                'key' => $key,
                'name' => $item['name'],
                'unit' => $item['unit'],
                'cost' => $this->engine->unitCost($state, $key, $state['event']),
                'fair' => $item['fair'],
                'price' => $state['prices'][$key],
                'stock' => $state['stock'][$key]['qty'],
                'perishable' => $item['spoil'] >= 0.3,
                'min_price' => (int) ($item['cost'] * 0.5),
                'max_price' => $item['fair'] * 3,
            ])->values()->all(),
            'promos' => collect(GameRules::PROMOS)->map(fn ($p, $key) => ['key' => $key, 'label' => $p['label'], 'cost' => $p['cost'], 'hint' => $p['hint']])->values()->all(),
            'special' => ['question' => $special['question'], 'options' => collect($special['options'])->map(fn ($o, $key) => ['key' => $key, 'label' => $o['label'], 'cost' => $o['cost'], 'hint' => $o['hint']])->values()->all()],
            'result' => $game->phase === 'laporan' ? $state['last_result'] : null,
            'totals' => ['profit' => $state['total_profit'], 'waste' => $state['waste_value']],
        ];
    }

    private function beginDay(ShopGame $game): void
    {
        $state = $game->state;
        $state['event'] = $this->events->pick($game, $game->day);
        $game->update(['state' => $state]);
    }

    private function finish(ShopGame $game): ShopGame
    {
        $final = $this->engine->finalScore($game->state);
        $game->update([
            'status' => GameStatus::Selesai,
            'score' => $final['score'],
            'ending' => $final['ending'],
            'phase' => 'selesai',
            'debrief' => $this->debrief($game, $final),
        ]);

        return $game;
    }

    private function debrief(ShopGame $game, array $final): array
    {
        $days = $game->days()->get();
        $log = $days->map(fn ($d) => 'Hari '.$d->day.' ('.GameRules::THEMES[$d->day]['title'].'): promosi '.$d->decisions['promo'].', keputusan '.GameRules::SPECIALS[$d->day]['options'][$d->decisions['special']]['label'].', terjual '.$d->result['sold_total'].', tidak kebagian '.$d->result['lost_total'].', terbuang Rp'.$d->result['waste_value'].', laba Rp'.$d->result['profit'].', kepuasan '.$d->result['satisfaction_after'])->implode("\n");

        try {
            $data = $this->ai->generateJson('shop_debrief', 'Jenis warung: '.GameRules::KINDS[$game->kind]['label']."\nNilai akhir: {$final['score']}\nCatatan tujuh hari:\n{$log}", FeatureSchemas::for('shop_debrief'), $game->user);

            return $data + ['parts' => $final['parts'], 'is_ai' => true];
        } catch (AiException) {
            return $this->fallbackDebrief($days) + ['parts' => $final['parts'], 'is_ai' => false];
        }
    }

    private function fallbackDebrief($days): array
    {
        $best = $days->sortByDesc(fn ($d) => $d->result['profit'])->first();
        $worst = $days->sortBy(fn ($d) => $d->result['profit'] - $d->result['waste_value'] - $d->result['lost_total'] * 500)->first();
        $label = fn ($d) => GameRules::SPECIALS[$d->day]['options'][$d->decisions['special']]['label'];
        $waste = $days->sum(fn ($d) => $d->result['waste_value']);
        $lost = $days->sum(fn ($d) => $d->result['lost_total']);
        $bus = $days->firstWhere('day', 4);

        $lessons = array_values(array_filter([
            $lost > 20 ? 'Siapkan stok lebih banyak di hari ramai seperti rombongan wisata dan hari pasaran. Pembeli yang tidak kebagian adalah untung yang hilang.' : null,
            $waste > 0 ? 'Barang cepat basi dibeli secukupnya, terutama saat hujan. Sisa yang terbuang mengurangi untung.' : null,
            $bus && $bus->decisions['special'] === 'naik' ? 'Menaikkan harga saat ramai memberi untung sesaat, tapi ulasan pelanggan bisa menurunkan penjualan hari-hari berikutnya.' : null,
            'Saat harga bahan naik, hitung ulang HPP lalu sesuaikan harga jual atau porsi dengan jujur.',
            'Sisakan kas untuk biaya harian supaya warung tidak kehabisan modal.',
        ]));

        return [
            'best' => 'Hari '.$best->day.': '.$label($best).'. Laba hari itu Rp'.number_format($best->result['profit'], 0, ',', '.').'.',
            'worst' => 'Hari '.$worst->day.': '.$label($worst).'. Laba Rp'.number_format($worst->result['profit'], 0, ',', '.').', '.$worst->result['lost_total'].' pembeli tidak kebagian.',
            'lessons' => array_slice($lessons, 0, 3),
        ];
    }

    private function expect(ShopGame $game, string $phase): void
    {
        if ($game->status !== GameStatus::Berjalan) {
            throw new GameException('Permainan ini sudah selesai. Mulai permainan baru untuk bermain lagi.');
        }
        if ($game->phase !== $phase) {
            throw new GameException('Tahap ini belum bisa dikerjakan. Selesaikan tahap yang sedang berjalan dulu.');
        }
    }

    private function save(ShopGame $game, array $state, string $phase): ShopGame
    {
        $game->update(['state' => $state, 'phase' => $phase]);

        return $game;
    }
}
