<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Menjalankan semua seeder...');

        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
        ]);

        $this->command->info('🎉 Semua seeder selesai!');
    }
}