<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Data acuan yang dibutuhkan aplikasi di semua lingkungan. */
    public function run(): void
    {
        $this->call([RoleSeeder::class, ReferenceSeeder::class]);
    }
}
