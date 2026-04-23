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
            ['kode' => 'KAT001', 'name' => 'SEMBAKO'],
            ['kode' => 'KAT002', 'name' => 'JAJANAN / SNACK'],
            ['kode' => 'KAT003', 'name' => 'KEBUTUHAN RUMAH TANGGA'],
            ['kode' => 'KAT004', 'name' => 'MINUMAN'],
        ];

        foreach ($productCategories as $cat) {
            Category::create([
                'kode_kategori' => $cat['kode'],
                'name'          => $cat['name'],
                'type'          => 'product',
            ]);
        }

       $supplierCategories = [
            ['kode' => 'SUP001', 'name' => 'SEMBAKO'],
            ['kode' => 'SUP002', 'name' => 'JAJANAN / SNACK'],
            ['kode' => 'SUP003', 'name' => 'KEBUTUHAN RUMAH TANGGA'],
            ['kode' => 'SUP004', 'name' => 'MINUMAN'],
        ];

        foreach ($supplierCategories as $cat) {
            Category::create([
                'kode_kategori' => $cat['kode'],
                'name'          => $cat['name'],
                'type'          => 'supplier',
            ]);
        }

        $this->command->info('✅ CategorySeeder: 8 kategori berhasil dibuat.');
    }
}