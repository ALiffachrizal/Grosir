<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Category::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $productCategories = [
            'Sembako',
            'Jajanan / Snack',
            'Kebutuhan Rumah Tangga',
            'Minuman',
        ];

        foreach ($productCategories as $name) {
            Category::create([
                'name' => $name,
                'type' => 'product',
            ]);
        }

        $supplierCategories = [
            'Sembako',
            'Jajanan / Snack',
            'Kebutuhan Rumah Tangga',
            'Minuman',
        ];

        foreach ($supplierCategories as $name) {
            Category::create([
                'name' => $name,
                'type' => 'supplier',
            ]);
        }

        $this->command->info('✅ CategorySeeder: 8 kategori berhasil dibuat.');
    }
}