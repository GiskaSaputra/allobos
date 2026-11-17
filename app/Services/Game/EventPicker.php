<?php

namespace App\Services\Game;

use App\Models\ShopEvent;
use App\Models\ShopGame;
use App\Services\Ai\AiException;
use App\Services\Ai\FeatureSchemas;
use App\Services\Ai\GeminiClient;

class EventPicker
{
    public function __construct(
        private readonly GeminiClient $ai,
        private readonly GameEngine $engine,
    ) {}

    /** @return array{title:string,story:string,effect:array,source:string} */
    public function pick(ShopGame $game, int $day): array
    {
        $theme = GameRules::THEMES[$day];
        $kind = GameRules::KINDS[$game->kind];

        try {
            $data = $this->ai->generateJson('shop_event', implode("\n", [
                'Hari ke-'.$day.' dari 7. Tema babak: '.$theme['title'].' ('.$theme['lesson'].').',
                'Jenis warung: '.$kind['label'].', menjual '.collect($kind['items'])->pluck('name')->implode(', ').'.',
                'Rentang efek yang sesuai tema: demand '.implode(' sampai ', $theme['demand']).', cost '.implode(' sampai ', $theme['cost']).', cash '.implode(' sampai ', $theme['cash']).'.',
                'Profil pemain: '.($game->user->profile?->forAi() ?? '-'),
            ]), FeatureSchemas::for('shop_event'), $game->user);

            return ['title' => mb_substr($data['title'], 0, 80), 'story' => mb_substr($data['story'], 0, 400), 'effect' => $this->engine->clampEffect($day, $data['effect']), 'source' => 'ai'];
        } catch (AiException) {
            return $this->fromBank($game, $day);
        }
    }

    /** Cadangan tanpa AI: bank kejadian (WB-05). */
    public function fromBank(ShopGame $game, int $day): array
    {
        $options = ShopEvent::query()->where('day', $day)->where(fn ($q) => $q->whereNull('kind')->orWhere('kind', $game->kind))->orderBy('id')->get();
        if ($options->isEmpty()) {
            $theme = GameRules::THEMES[$day];

            return ['title' => $theme['title'], 'story' => $theme['lesson'].'.', 'effect' => $this->engine->clampEffect($day, ['demand' => array_sum($theme['demand']) / 2, 'cost' => $theme['cost'][1], 'cash' => 0]), 'source' => 'bank'];
        }

        $event = $options[(int) floor((new Rng($game->seed.'-event-'.$day))->float() * $options->count())];

        return [
            'title' => $event->title,
            'story' => $event->story,
            'effect' => $this->engine->clampEffect($day, ['demand' => $event->demand, 'cost' => $event->cost, 'cash' => $event->cash]),
            'source' => 'bank',
        ];
    }
}
