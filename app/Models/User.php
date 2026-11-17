<?php

namespace App\Models;

use App\Enums\RoleName;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = ['name', 'email', 'whatsapp', 'password', 'active_role', 'is_active', 'is_demo', 'email_verified_at'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active_role' => RoleName::class,
            'is_active' => 'boolean',
            'is_demo' => 'boolean',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(ResidentProfile::class);
    }

    public function bossProfile(): HasOne
    {
        return $this->hasOne(BossProfile::class);
    }

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }

    public function plan(): HasOne
    {
        return $this->hasOne(BusinessPlan::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(PlanStep::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /** @return list<RoleName> */
    public function roleOptions(): array
    {
        return array_values(array_filter(RoleName::cases(), fn (RoleName $role) => $this->hasRole($role->value)));
    }

    public function initials(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)))
            ->filter()
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)).'.')
            ->implode(' ');
    }

    public function firstName(): string
    {
        return strtok($this->name, ' ') ?: $this->name;
    }

    /** Nomor WhatsApp dalam format 62xxxx untuk tautan wa.me. */
    public static function normalizeWhatsapp(?string $number): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $number);

        return match (true) {
            $digits === '' => null,
            str_starts_with($digits, '62') => $digits,
            str_starts_with($digits, '0') => '62'.substr($digits, 1),
            default => '62'.$digits,
        };
    }
}
