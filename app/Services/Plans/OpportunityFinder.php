<?php

namespace App\Services\Plans;

use App\Models\BusinessField;
use App\Models\ConsultNote;
use App\Models\Opportunity;
use App\Models\ResidentProfile;
use App\Models\User;
use App\Services\Ai\AiException;
use App\Services\Ai\FeatureSchemas;
use App\Services\Ai\GeminiClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class OpportunityFinder
{
    // Contoh usaha per bidang untuk cadangan tanpa AI.
    private const IDEAS = [
        'kuliner-dan-makanan-olahan' => ['Keripik dan camilan olahan singkong', 'Harga singkong bisa naik saat kemarau'],
        'pertanian-dan-perkebunan' => ['Bibit sayur dan tanaman pekarangan', 'Hasil panen bergantung musim hujan'],
        'peternakan-dan-perikanan' => ['Ternak kambing atau budidaya lele kolam terpal', 'Ternak sakit bisa menghabiskan modal'],
        'kerajinan-tangan' => ['Kerajinan kayu dan topeng untuk suvenir', 'Butuh waktu membuat contoh produk yang laku'],
        'fesyen-dan-batik' => ['Jahit dan batik motif lokal Gunungkidul', 'Pesaing batik pabrikan lebih murah'],
        'pariwisata-dan-penginapan' => ['Homestay dan jasa pemandu wisata pantai', 'Ramai hanya di musim liburan'],
        'perdagangan-dan-kelontong' => ['Warung kelontong kebutuhan harian', 'Perputaran uang kecil bila stok terlalu banyak'],
        'oleh-oleh-dan-suvenir' => ['Paket oleh-oleh khas Gunungkidul', 'Pembeli bergantung jumlah wisatawan'],
        'jasa-dan-perbaikan' => ['Jasa servis dan reparasi kecil', 'Pelanggan baru datang dari mulut ke mulut'],
        'kecantikan-dan-kesehatan' => ['Jamu dan minuman herbal kemasan', 'Perlu izin PIRT sebelum dijual luas'],
        'pendidikan-dan-pelatihan' => ['Les belajar anak di rumah', 'Jumlah murid naik turun mengikuti tahun ajaran'],
        'kreatif-dan-digital' => ['Jasa foto produk dan konten jualan UMKM', 'Perlu contoh karya untuk meyakinkan pelanggan'],
        'transportasi-dan-logistik' => ['Jasa antar barang dan titip beli antar desa', 'Biaya bensin dan perawatan kendaraan'],
    ];

    private const CAPITAL = [
        'lt1' => 'Rp300 ribu sampai Rp1 juta',
        '1-5' => 'Rp1 juta sampai Rp5 juta',
        '5-15' => 'Rp5 juta sampai Rp15 juta',
        'gt15' => 'Di atas Rp15 juta',
    ];

    public function __construct(
        private readonly GeminiClient $ai,
        private readonly LearningContext $context,
    ) {}

    /** @return Collection<int, Opportunity> */
    public function generate(User $user): Collection
    {
        $fields = BusinessField::query()->usaha()->get();
        $batch = (string) Str::uuid();

        try {
            $data = $this->ai->generateJson('opportunity', $this->context->for($user)."\n\nPilihan slug bidang usaha: ".$fields->pluck('slug')->implode(', '), FeatureSchemas::for('opportunity', ['fields' => $fields->pluck('slug')->all()]), $user);
            $items = collect($data['items'])->take(3)->map(fn ($item) => $item + ['is_ai' => true]);
        } catch (AiException $e) {
            if ($e->isQuota()) {
                throw $e;
            }
            $items = $this->fallback($user, $fields);
        }

        return $items->map(fn ($item) => Opportunity::query()->create([
            'user_id' => $user->id,
            'batch' => $batch,
            'title' => Str::limit($item['title'], 250, ''),
            'why' => $item['why'],
            'capital_range' => Str::limit($item['capital_range'], 250, ''),
            'risk' => $item['risk'],
            'ipi_field' => $item['ipi_field'],
            'is_ai' => $item['is_ai'],
        ]))->values();
    }

    /** Pencocokan kata kunci profil dan riwayat terhadap bidang IPI. */
    private function fallback(User $user, Collection $fields): Collection
    {
        $profile = $user->profile;
        $interests = $profile->interests ?? [];
        $haystack = Str::lower(implode(' ', [
            $profile->skills, $profile->existing_business, $profile->district?->potential,
            ConsultNote::query()->where('user_id', $user->id)->latest()->limit(3)->pluck('problem')->implode(' '),
        ]));

        return $fields
            ->map(function (BusinessField $field) use ($interests, $haystack) {
                $hits = collect(explode(' ', $field->keywords))->filter(fn ($word) => $word !== '' && str_contains($haystack, $word));

                return ['field' => $field, 'score' => (in_array($field->id, $interests, true) ? 5 : 0) + $hits->count(), 'hits' => $hits];
            })
            ->sortByDesc('score')->take(3)->values()
            ->map(function (array $match) use ($profile) {
                [$title, $risk] = self::IDEAS[$match['field']->slug] ?? ['Usaha '.Str::lower($match['field']->name), 'Pembeli awal masih sedikit'];
                $reasons = array_filter([
                    in_array($match['field']->id, $profile->interests ?? [], true) ? 'kamu memilih bidang '.$match['field']->name.' saat mengisi profil' : null,
                    $match['hits']->isNotEmpty() ? 'cocok dengan kata "'.$match['hits']->take(2)->implode('", "').'" di profil dan catatanmu' : null,
                    $profile->district ? 'potensi Kapanewon '.$profile->district->name.': '.Str::lower($profile->district->potential) : null,
                ]);

                return [
                    'title' => $title,
                    'why' => Str::ucfirst(implode(', ', $reasons) ?: 'bidang ini umum di Gunungkidul').'.',
                    'capital_range' => self::CAPITAL[$profile->capital_range] ?? 'Sesuaikan dengan modalmu',
                    'risk' => $risk,
                    'ipi_field' => $match['field']->slug,
                    'is_ai' => false,
                ];
            });
    }

    public function custom(User $user, string $title, ?string $why): Opportunity
    {
        Opportunity::query()->where('user_id', $user->id)->update(['chosen_at' => null]);

        return Opportunity::query()->create([
            'user_id' => $user->id,
            'batch' => (string) Str::uuid(),
            'title' => $title,
            'why' => $why ?: 'Ide usaha yang kamu tulis sendiri.',
            'capital_range' => ResidentProfile::CAPITAL[$user->profile->capital_range] ?? '-',
            'risk' => 'Belum dinilai. Bahas di Ruang Bos atau Temu Bos.',
            'is_ai' => false,
            'is_custom' => true,
            'chosen_at' => now(),
        ]);
    }

    public function choose(Opportunity $opportunity): void
    {
        Opportunity::query()->where('user_id', $opportunity->user_id)->update(['chosen_at' => null]);
        $opportunity->update(['chosen_at' => now()]);
    }
}
