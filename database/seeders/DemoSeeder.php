<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Akun dan data contoh untuk penjurian. Semua data di sini berlabel Demo. */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DatabaseSeeder::class);

        foreach ($this->accounts() as [$role, $name, $email]) {
            $user = User::query()->updateOrCreate(['email' => $email], [
                'name' => $name,
                'whatsapp' => '6281200000000',
                'password' => env('DEMO_PASSWORD', 'allobos-demo'),
                'email_verified_at' => now(),
                'active_role' => $role,
                'is_active' => true,
                'is_demo' => true,
            ]);
            $user->syncRoles([$role->value]);
        }
    }

    /** @return list<array{RoleName,string,string}> */
    private function accounts(): array
    {
        return [
            [RoleName::Warga, 'Sri Lestari', 'warga@allobos.test'],
            [RoleName::Bos, 'Arif Wibowo', 'bos@allobos.test'],
            [RoleName::Pengelola, 'Rani Pratiwi', 'pengelola@allobos.test'],
            [RoleName::Admin, 'Admin alloBos', 'admin@allobos.test'],
        ];
    }
}
