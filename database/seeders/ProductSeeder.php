<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\User;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        $products = [
            /*
            |--------------------------------------------------------------------------
            | SEMBAKO - KAT001
            |--------------------------------------------------------------------------
            */
            ['name' => 'Mie Instan Kuah', 'category_code' => 'SBK001', 'base_unit' => 'PCS', 'items_per_package' => 40, 'items_per_bundle' => 5, 'stock' => 120, 'minimum_stock' => 30, 'purchase_price' => 2800, 'selling_price' => 3500],
            ['name' => 'Mie Instan Goreng', 'category_code' => 'SBK001', 'base_unit' => 'PCS', 'items_per_package' => 40, 'items_per_bundle' => 5, 'stock' => 18, 'minimum_stock' => 30, 'purchase_price' => 2900, 'selling_price' => 3600],
            ['name' => 'Beras Premium', 'category_code' => 'SBK001', 'base_unit' => 'KG', 'items_per_package' => 25, 'items_per_bundle' => 1, 'stock' => 100, 'minimum_stock' => 25, 'purchase_price' => 14000, 'selling_price' => 16000],
            ['name' => 'Beras IR64', 'category_code' => 'SBK001', 'base_unit' => 'KG', 'items_per_package' => 25, 'items_per_bundle' => 1, 'stock' => 60, 'minimum_stock' => 20, 'purchase_price' => 11500, 'selling_price' => 13000],
            ['name' => 'Gula Pasir', 'category_code' => 'SBK001', 'base_unit' => 'KG', 'items_per_package' => 50, 'items_per_bundle' => 1, 'stock' => 80, 'minimum_stock' => 20, 'purchase_price' => 15000, 'selling_price' => 17000],
            ['name' => 'Gula Merah', 'category_code' => 'SBK001', 'base_unit' => 'KG', 'items_per_package' => 20, 'items_per_bundle' => 1, 'stock' => 14, 'minimum_stock' => 15, 'purchase_price' => 17000, 'selling_price' => 19500],
            ['name' => 'Minyak Goreng 1 Liter', 'category_code' => 'SBK001', 'base_unit' => 'LITER', 'items_per_package' => 12, 'items_per_bundle' => 6, 'stock' => 72, 'minimum_stock' => 18, 'purchase_price' => 16000, 'selling_price' => 18000],
            ['name' => 'Minyak Goreng 2 Liter', 'category_code' => 'SBK001', 'base_unit' => 'LITER', 'items_per_package' => 6, 'items_per_bundle' => 6, 'stock' => 36, 'minimum_stock' => 12, 'purchase_price' => 30000, 'selling_price' => 34000],
            ['name' => 'Tepung Terigu', 'category_code' => 'SBK001', 'base_unit' => 'KG', 'items_per_package' => 20, 'items_per_bundle' => 1, 'stock' => 45, 'minimum_stock' => 15, 'purchase_price' => 9500, 'selling_price' => 11000],
            ['name' => 'Telur Ayam', 'category_code' => 'SBK001', 'base_unit' => 'KG', 'items_per_package' => 15, 'items_per_bundle' => 1, 'stock' => 30, 'minimum_stock' => 10, 'purchase_price' => 26000, 'selling_price' => 29000],

            /*
            |--------------------------------------------------------------------------
            | JAJANAN / SNACK - KAT002
            |--------------------------------------------------------------------------
            */
            ['name' => 'Biskuit Cokelat', 'category_code' => 'JJN002', 'base_unit' => 'PCS', 'items_per_package' => 24, 'items_per_bundle' => 6, 'stock' => 48, 'minimum_stock' => 12, 'purchase_price' => 4500, 'selling_price' => 6000],
            ['name' => 'Keripik Singkong', 'category_code' => 'JJN002', 'base_unit' => 'PCS', 'items_per_package' => 20, 'items_per_bundle' => 5, 'stock' => 8, 'minimum_stock' => 15, 'purchase_price' => 5000, 'selling_price' => 7000],
            ['name' => 'Wafer Cokelat', 'category_code' => 'JJN002', 'base_unit' => 'PCS', 'items_per_package' => 24, 'items_per_bundle' => 6, 'stock' => 60, 'minimum_stock' => 18, 'purchase_price' => 3000, 'selling_price' => 4000],
            ['name' => 'Kerupuk Udang', 'category_code' => 'JJN002', 'base_unit' => 'PCS', 'items_per_package' => 30, 'items_per_bundle' => 5, 'stock' => 40, 'minimum_stock' => 15, 'purchase_price' => 3500, 'selling_price' => 4500],
            ['name' => 'Permen Mint', 'category_code' => 'JJN002', 'base_unit' => 'PCS', 'items_per_package' => 100, 'items_per_bundle' => 10, 'stock' => 200, 'minimum_stock' => 50, 'purchase_price' => 300, 'selling_price' => 500],
            ['name' => 'Coklat Batang', 'category_code' => 'JJN002', 'base_unit' => 'PCS', 'items_per_package' => 20, 'items_per_bundle' => 5, 'stock' => 25, 'minimum_stock' => 10, 'purchase_price' => 6000, 'selling_price' => 8000],
            ['name' => 'Kacang Atom', 'category_code' => 'JJN002', 'base_unit' => 'PCS', 'items_per_package' => 24, 'items_per_bundle' => 6, 'stock' => 5, 'minimum_stock' => 12, 'purchase_price' => 4000, 'selling_price' => 5500],
            ['name' => 'Marshmallow', 'category_code' => 'JJN002', 'base_unit' => 'PCS', 'items_per_package' => 12, 'items_per_bundle' => 6, 'stock' => 20, 'minimum_stock' => 10, 'purchase_price' => 7000, 'selling_price' => 9500],

            /*
            |--------------------------------------------------------------------------
            | KEBUTUHAN RUMAH TANGGA - KAT003
            |--------------------------------------------------------------------------
            */
            ['name' => 'Deterjen Sachet', 'category_code' => 'RMH003', 'base_unit' => 'PCS', 'items_per_package' => 48, 'items_per_bundle' => 6, 'stock' => 6, 'minimum_stock' => 10, 'purchase_price' => 2000, 'selling_price' => 3000],
            ['name' => 'Sabun Cuci Piring', 'category_code' => 'RMH003', 'base_unit' => 'BOTOL', 'items_per_package' => 12, 'items_per_bundle' => 6, 'stock' => 36, 'minimum_stock' => 12, 'purchase_price' => 9000, 'selling_price' => 11000],
            ['name' => 'Tisu Gulung', 'category_code' => 'RMH003', 'base_unit' => 'PCS', 'items_per_package' => 10, 'items_per_bundle' => 5, 'stock' => 40, 'minimum_stock' => 10, 'purchase_price' => 4500, 'selling_price' => 6000],
            ['name' => 'Sabun Mandi Batang', 'category_code' => 'RMH003', 'base_unit' => 'PCS', 'items_per_package' => 48, 'items_per_bundle' => 6, 'stock' => 55, 'minimum_stock' => 15, 'purchase_price' => 2200, 'selling_price' => 3200],
            ['name' => 'Pembersih Lantai', 'category_code' => 'RMH003', 'base_unit' => 'BOTOL', 'items_per_package' => 12, 'items_per_bundle' => 6, 'stock' => 24, 'minimum_stock' => 10, 'purchase_price' => 8500, 'selling_price' => 10500],
            ['name' => 'Kantong Plastik', 'category_code' => 'RMH003', 'base_unit' => 'PCS', 'items_per_package' => 100, 'items_per_bundle' => 10, 'stock' => 300, 'minimum_stock' => 50, 'purchase_price' => 150, 'selling_price' => 300],
            ['name' => 'Korek Api', 'category_code' => 'RMH003', 'base_unit' => 'PCS', 'items_per_package' => 60, 'items_per_bundle' => 10, 'stock' => 9, 'minimum_stock' => 20, 'purchase_price' => 500, 'selling_price' => 1000],
            ['name' => 'Baterai AA', 'category_code' => 'RMH003', 'base_unit' => 'PCS', 'items_per_package' => 40, 'items_per_bundle' => 4, 'stock' => 48, 'minimum_stock' => 16, 'purchase_price' => 3500, 'selling_price' => 5000],

            /*
            |--------------------------------------------------------------------------
            | MINUMAN - KAT004
            |--------------------------------------------------------------------------
            */
            ['name' => 'Air Mineral 600 ml', 'category_code' => 'MNM004', 'base_unit' => 'BOTOL', 'items_per_package' => 24, 'items_per_bundle' => 6, 'stock' => 144, 'minimum_stock' => 48, 'purchase_price' => 2500, 'selling_price' => 3500],
            ['name' => 'Teh Botol 450 ml', 'category_code' => 'MNM004', 'base_unit' => 'BOTOL', 'items_per_package' => 24, 'items_per_bundle' => 6, 'stock' => 96, 'minimum_stock' => 24, 'purchase_price' => 4000, 'selling_price' => 5000],
            ['name' => 'Kopi Sachet', 'category_code' => 'MNM004', 'base_unit' => 'PCS', 'items_per_package' => 120, 'items_per_bundle' => 10, 'stock' => 240, 'minimum_stock' => 60, 'purchase_price' => 1500, 'selling_price' => 2000],
            ['name' => 'Susu Kental Manis', 'category_code' => 'MNM004', 'base_unit' => 'PCS', 'items_per_package' => 48, 'items_per_bundle' => 6, 'stock' => 40, 'minimum_stock' => 15, 'purchase_price' => 9500, 'selling_price' => 11500],
            ['name' => 'Minuman Isotonik', 'category_code' => 'MNM004', 'base_unit' => 'BOTOL', 'items_per_package' => 24, 'items_per_bundle' => 6, 'stock' => 12, 'minimum_stock' => 24, 'purchase_price' => 4500, 'selling_price' => 6000],
            ['name' => 'Sirup Marjan', 'category_code' => 'MNM004', 'base_unit' => 'BOTOL', 'items_per_package' => 12, 'items_per_bundle' => 6, 'stock' => 18, 'minimum_stock' => 8, 'purchase_price' => 17000, 'selling_price' => 20000],
            ['name' => 'Susu UHT Kotak', 'category_code' => 'MNM004', 'base_unit' => 'PCS', 'items_per_package' => 24, 'items_per_bundle' => 6, 'stock' => 30, 'minimum_stock' => 12, 'purchase_price' => 5500, 'selling_price' => 7000],
            ['name' => 'Teh Celup', 'category_code' => 'MNM004', 'base_unit' => 'PCS', 'items_per_package' => 50, 'items_per_bundle' => 5, 'stock' => 70, 'minimum_stock' => 20, 'purchase_price' => 800, 'selling_price' => 1200],

            /*
            |--------------------------------------------------------------------------
            | BUMBU DAPUR - KAT005
            |--------------------------------------------------------------------------
            */
            ['name' => 'Bawang Merah', 'category_code' => 'BMD005', 'base_unit' => 'KG', 'items_per_package' => 20, 'items_per_bundle' => 1, 'stock' => 25, 'minimum_stock' => 10, 'purchase_price' => 28000, 'selling_price' => 32000],
            ['name' => 'Bawang Putih', 'category_code' => 'BMD005', 'base_unit' => 'KG', 'items_per_package' => 20, 'items_per_bundle' => 1, 'stock' => 22, 'minimum_stock' => 10, 'purchase_price' => 30000, 'selling_price' => 34000],
            ['name' => 'Cabai Merah', 'category_code' => 'BMD005', 'base_unit' => 'KG', 'items_per_package' => 15, 'items_per_bundle' => 1, 'stock' => 8, 'minimum_stock' => 10, 'purchase_price' => 35000, 'selling_price' => 40000],
            ['name' => 'Garam Dapur', 'category_code' => 'BMD005', 'base_unit' => 'PCS', 'items_per_package' => 40, 'items_per_bundle' => 5, 'stock' => 60, 'minimum_stock' => 15, 'purchase_price' => 2000, 'selling_price' => 3000],
            ['name' => 'Merica Bubuk', 'category_code' => 'BMD005', 'base_unit' => 'PCS', 'items_per_package' => 50, 'items_per_bundle' => 5, 'stock' => 35, 'minimum_stock' => 12, 'purchase_price' => 3000, 'selling_price' => 4500],
            ['name' => 'Penyedap Rasa Sachet', 'category_code' => 'BMD005', 'base_unit' => 'PCS', 'items_per_package' => 100, 'items_per_bundle' => 10, 'stock' => 150, 'minimum_stock' => 40, 'purchase_price' => 300, 'selling_price' => 500],
            ['name' => 'Santan Instan', 'category_code' => 'BMD005', 'base_unit' => 'PCS', 'items_per_package' => 48, 'items_per_bundle' => 6, 'stock' => 15, 'minimum_stock' => 15, 'purchase_price' => 2500, 'selling_price' => 3500],
            ['name' => 'Kemiri', 'category_code' => 'BMD005', 'base_unit' => 'KG', 'items_per_package' => 10, 'items_per_bundle' => 1, 'stock' => 6, 'minimum_stock' => 8, 'purchase_price' => 45000, 'selling_price' => 52000],

            /*
            |--------------------------------------------------------------------------
            | PERAWATAN TUBUH - KAT006
            |--------------------------------------------------------------------------
            */
            ['name' => 'Sabun Mandi Cair', 'category_code' => 'PRT006', 'base_unit' => 'BOTOL', 'items_per_package' => 12, 'items_per_bundle' => 6, 'stock' => 30, 'minimum_stock' => 12, 'purchase_price' => 12000, 'selling_price' => 15000],
            ['name' => 'Sampo Sachet', 'category_code' => 'PRT006', 'base_unit' => 'PCS', 'items_per_package' => 100, 'items_per_bundle' => 10, 'stock' => 180, 'minimum_stock' => 40, 'purchase_price' => 500, 'selling_price' => 800],
            ['name' => 'Pasta Gigi', 'category_code' => 'PRT006', 'base_unit' => 'PCS', 'items_per_package' => 24, 'items_per_bundle' => 6, 'stock' => 40, 'minimum_stock' => 15, 'purchase_price' => 6500, 'selling_price' => 8500],
            ['name' => 'Sikat Gigi', 'category_code' => 'PRT006', 'base_unit' => 'PCS', 'items_per_package' => 50, 'items_per_bundle' => 10, 'stock' => 7, 'minimum_stock' => 15, 'purchase_price' => 2500, 'selling_price' => 4000],
            ['name' => 'Tisu Basah', 'category_code' => 'PRT006', 'base_unit' => 'PCS', 'items_per_package' => 24, 'items_per_bundle' => 6, 'stock' => 28, 'minimum_stock' => 12, 'purchase_price' => 5000, 'selling_price' => 7000],
            ['name' => 'Pembalut Wanita', 'category_code' => 'PRT006', 'base_unit' => 'PCS', 'items_per_package' => 40, 'items_per_bundle' => 5, 'stock' => 50, 'minimum_stock' => 20, 'purchase_price' => 8000, 'selling_price' => 11000],
            ['name' => 'Popok Bayi', 'category_code' => 'PRT006', 'base_unit' => 'PCS', 'items_per_package' => 20, 'items_per_bundle' => 4, 'stock' => 10, 'minimum_stock' => 12, 'purchase_price' => 45000, 'selling_price' => 55000],
            ['name' => 'Hand Sanitizer', 'category_code' => 'PRT006', 'base_unit' => 'BOTOL', 'items_per_package' => 24, 'items_per_bundle' => 6, 'stock' => 15, 'minimum_stock' => 10, 'purchase_price' => 6000, 'selling_price' => 8500],
        ];

        foreach ($products as $productData) {
            $category = Category::where(
                'kode_kategori',
                $productData['category_code']
            )->firstOrFail();

            // kode_produk tidak perlu diisi di sini — otomatis di-generate
            // oleh event creating di model Product saat produk baru dibuat
            $product = Product::updateOrCreate(
                [
                    'name' => $productData['name'],
                ],
                [
                    'category_id'       => $category->id,
                    'base_unit'         => $productData['base_unit'],
                    'items_per_package' => $productData['items_per_package'],
                    'items_per_bundle'  => $productData['items_per_bundle'],
                    'stock'             => $productData['stock'],
                    'minimum_stock'     => $productData['minimum_stock'],
                    'purchase_price'    => $productData['purchase_price'],
                    'selling_price'     => $productData['selling_price'],
                ]
            );

            if ($product->stock > 0) {
                StockLog::updateOrCreate(
                    [
                        'kode_produk'    => $product->kode_produk,
                        'type'           => 'in',
                        'reference_type' => 'initial_stock',
                        'reference_id'   => $product->id,
                    ],
                    [
                        'user_id'  => $admin->id,
                        'quantity' => $product->stock,
                        'note'     => 'Stok awal produk dari seeder',
                    ]
                );
            }
        }

        $totalProducts = count($products);
        $totalMenipis = collect($products)->filter(
            fn ($p) => $p['stock'] <= $p['minimum_stock']
        )->count();

        $this->command->info("✅ ProductSeeder: {$totalProducts} produk berhasil dibuat ({$totalMenipis} di antaranya stok menipis).");
    }
}