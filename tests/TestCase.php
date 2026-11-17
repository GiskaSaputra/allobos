<?php

namespace Tests;

use App\Enums\RoleName;
use App\Models\BusinessField;
use App\Models\District;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected bool $seed = true;

    protected string $seeder = DatabaseSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    protected function userWithRole(RoleName $role = RoleName::Warga, array $attributes = []): User
    {
        return User::factory()->role($role)->create($attributes);
    }

    /** Warga dengan Profil Usaha lengkap (Ponjong, kuliner, modal kecil). */
    protected function onboardedWarga(array $profile = [], array $attributes = []): User
    {
        $user = $this->userWithRole(RoleName::Warga, $attributes);
        $user->profile()->create($profile + [
            'district_id' => District::query()->where('slug', 'ponjong')->value('id'),
            'business_status' => 'sudah',
            'existing_business' => 'Keripik singkong',
            'interests' => [BusinessField::query()->where('slug', 'kuliner-dan-makanan-olahan')->value('id')],
            'capital_range' => 'lt1',
            'free_time' => '2-4',
            'skills' => 'Memasak dan menggoreng',
            'step' => 5,
            'onboarded_at' => now(),
        ]);

        return $user->fresh();
    }

    /** Jawaban Gemini palsu berurutan, satu payload JSON untuk tiap panggilan. */
    protected function fakeGemini(array ...$payloads): void
    {
        config(['ai.keys' => ['kunci-uji']]);
        $sequence = Http::sequence();
        foreach ($payloads as $payload) {
            $sequence->push([
                'candidates' => [['content' => ['parts' => [['text' => json_encode($payload)]]]]],
                'usageMetadata' => ['totalTokenCount' => 42],
            ]);
        }
        Http::fake(['generativelanguage.googleapis.com/*' => $sequence]);
    }
}
