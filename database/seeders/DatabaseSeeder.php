<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            KosSeeder::class, // hapus baris ini kalau tidak mau data kos contoh
        ]);
    }
}