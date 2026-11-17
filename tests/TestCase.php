<?php

namespace Tests;

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

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
}
