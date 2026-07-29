<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Kategori bertipe 'product' — dipakai bersama oleh produk & supplier.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Category::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categories = [
            ['kode' => 'SBK001', 'name' => 'SEMBAKO'],
            ['kode' => 'JJN002', 'name' => 'JAJANAN / SNACK'],
            ['kode' => 'RMH003', 'name' => 'KEBUTUHAN RUMAH TANGGA'],
            ['kode' => 'MNM004', 'name' => 'MINUMAN'],
            ['kode' => 'BMD005', 'name' => 'BUMBU DAPUR'],
            ['kode' => 'PRT006', 'name' => 'PERAWATAN TUBUH'],
        ];

        foreach ($categories as $cat) {
            Category::create([
                'kode_kategori' => $cat['kode'],
                'name'          => $cat['name'],
                'type'          => 'product',
            ]);
        }

        $this->command->info('✅ CategorySeeder: 6 kategori berhasil dibuat.');
    }
}