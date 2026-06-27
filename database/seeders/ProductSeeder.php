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
            [
                'name'              => 'Mie Instan Kuah',
                'category_code'     => 'KAT001',
                'base_unit'         => 'PCS',
                'items_per_package' => 40,
                'items_per_bundle'  => 5,
                'stock'             => 120,
                'minimum_stock'     => 30,
                'purchase_price'    => 2800,
                'selling_price'     => 3500,
            ],
            [
                'name'              => 'Beras Premium',
                'category_code'     => 'KAT001',
                'base_unit'         => 'KG',
                'items_per_package' => 25,
                'items_per_bundle'  => 1,
                'stock'             => 100,
                'minimum_stock'     => 25,
                'purchase_price'    => 14000,
                'selling_price'     => 16000,
            ],
            [
                'name'              => 'Gula Pasir',
                'category_code'     => 'KAT001',
                'base_unit'         => 'KG',
                'items_per_package' => 50,
                'items_per_bundle'  => 1,
                'stock'             => 80,
                'minimum_stock'     => 20,
                'purchase_price'    => 15000,
                'selling_price'     => 17000,
            ],
            [
                'name'              => 'Minyak Goreng 1 Liter',
                'category_code'     => 'KAT001',
                'base_unit'         => 'LITER',
                'items_per_package' => 12,
                'items_per_bundle'  => 6,
                'stock'             => 72,
                'minimum_stock'     => 18,
                'purchase_price'    => 16000,
                'selling_price'     => 18000,
            ],

            /*
            |--------------------------------------------------------------------------
            | JAJANAN / SNACK - KAT002
            |--------------------------------------------------------------------------
            */
            [
                'name'              => 'Biskuit Cokelat',
                'category_code'     => 'KAT002',
                'base_unit'         => 'PCS',
                'items_per_package' => 24,
                'items_per_bundle'  => 6,
                'stock'             => 48,
                'minimum_stock'     => 12,
                'purchase_price'    => 4500,
                'selling_price'     => 6000,
            ],
            [
                'name'              => 'Keripik Singkong',
                'category_code'     => 'KAT002',
                'base_unit'         => 'PCS',
                'items_per_package' => 20,
                'items_per_bundle'  => 5,
                'stock'             => 8,
                'minimum_stock'     => 15,
                'purchase_price'    => 5000,
                'selling_price'     => 7000,
            ],
            [
                'name'              => 'Wafer Cokelat',
                'category_code'     => 'KAT002',
                'base_unit'         => 'PCS',
                'items_per_package' => 24,
                'items_per_bundle'  => 6,
                'stock'             => 60,
                'minimum_stock'     => 18,
                'purchase_price'    => 3000,
                'selling_price'     => 4000,
            ],

            /*
            |--------------------------------------------------------------------------
            | KEBUTUHAN RUMAH TANGGA - KAT003
            |--------------------------------------------------------------------------
            */
            [
                'name'              => 'Deterjen Sachet',
                'category_code'     => 'KAT003',
                'base_unit'         => 'PCS',
                'items_per_package' => 48,
                'items_per_bundle'  => 6,
                'stock'             => 6,
                'minimum_stock'     => 10,
                'purchase_price'    => 2000,
                'selling_price'     => 3000,
            ],
            [
                'name'              => 'Sabun Cuci Piring',
                'category_code'     => 'KAT003',
                'base_unit'         => 'BOTOL',
                'items_per_package' => 12,
                'items_per_bundle'  => 6,
                'stock'             => 36,
                'minimum_stock'     => 12,
                'purchase_price'    => 9000,
                'selling_price'     => 11000,
            ],
            [
                'name'              => 'Tisu Gulung',
                'category_code'     => 'KAT003',
                'base_unit'         => 'PCS',
                'items_per_package' => 10,
                'items_per_bundle'  => 5,
                'stock'             => 40,
                'minimum_stock'     => 10,
                'purchase_price'    => 4500,
                'selling_price'     => 6000,
            ],

            /*
            |--------------------------------------------------------------------------
            | MINUMAN - KAT004
            |--------------------------------------------------------------------------
            */
            [
                'name'              => 'Air Mineral 600 ml',
                'category_code'     => 'KAT004',
                'base_unit'         => 'BOTOL',
                'items_per_package' => 24,
                'items_per_bundle'  => 6,
                'stock'             => 144,
                'minimum_stock'     => 48,
                'purchase_price'    => 2500,
                'selling_price'     => 3500,
            ],
            [
                'name'              => 'Teh Botol 450 ml',
                'category_code'     => 'KAT004',
                'base_unit'         => 'BOTOL',
                'items_per_package' => 24,
                'items_per_bundle'  => 6,
                'stock'             => 96,
                'minimum_stock'     => 24,
                'purchase_price'    => 4000,
                'selling_price'     => 5000,
            ],
            [
                'name'              => 'Kopi Sachet',
                'category_code'     => 'KAT004',
                'base_unit'         => 'PCS',
                'items_per_package' => 120,
                'items_per_bundle'  => 10,
                'stock'             => 240,
                'minimum_stock'     => 60,
                'purchase_price'    => 1500,
                'selling_price'     => 2000,
            ],
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

        $this->command->info('✅ ProductSeeder: 13 produk dan stok awal berhasil dibuat.');
    }
}