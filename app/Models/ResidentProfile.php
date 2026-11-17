<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class ResidentProfile extends Model
{
    public const STEPS = [1 => 'Kapanewon', 2 => 'Status usaha', 3 => 'Bidang minat', 4 => 'Modal dan waktu', 5 => 'Keahlian'];

    public const CAPITAL = [
        'lt1' => 'Di bawah Rp1 juta',
        '1-5' => 'Rp1 juta sampai Rp5 juta',
        '5-15' => 'Rp5 juta sampai Rp15 juta',
        'gt15' => 'Di atas Rp15 juta',
    ];

    public const FREE_TIME = [
        'lt2' => 'Kurang dari 2 jam sehari',
        '2-4' => '2 sampai 4 jam sehari',
        '4-8' => '4 sampai 8 jam sehari',
        'full' => 'Seharian penuh',
    ];

    protected $fillable = ['user_id', 'district_id', 'business_status', 'existing_business', 'interests', 'capital_range', 'free_time', 'skills', 'step', 'onboarded_at'];

    protected function casts(): array
    {
        return ['interests' => 'array', 'onboarded_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /** @return Collection<int, BusinessField> */
    public function interestFields(): Collection
    {
        return BusinessField::query()->whereIn('id', $this->interests ?? [])->orderBy('sort')->get();
    }

    public function nextStep(): int
    {
        return min(5, max(1, $this->step));
    }

    public function isComplete(): bool
    {
        return $this->district_id && $this->business_status && ! empty($this->interests)
            && $this->capital_range && $this->free_time && filled($this->skills);
    }

    /** Ringkasan profil yang aman dikirim ke AI (tanpa data pribadi). */
    public function forAi(): string
    {
        return implode("\n", array_filter([
            'Kapanewon: '.($this->district?->name ?? '-'),
            'Potensi lokal: '.($this->district?->potential ?? '-'),
            'Status usaha: '.($this->business_status === 'sudah' ? 'sudah punya usaha ('.$this->existing_business.')' : 'belum punya usaha'),
            'Bidang minat: '.$this->interestFields()->pluck('name')->implode(', '),
            'Modal: '.(self::CAPITAL[$this->capital_range] ?? '-'),
            'Waktu luang: '.(self::FREE_TIME[$this->free_time] ?? '-'),
            'Keahlian: '.($this->skills ?? '-'),
        ]));
    }
}
