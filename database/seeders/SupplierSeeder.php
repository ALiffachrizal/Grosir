<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'kode_supplier' => 'SPL001',
                'name'          => 'PT Sumber Pangan Jaya',
                'phone'         => '081234567801',
                'category_code' => 'SUP001',
            ],
            [
                'kode_supplier' => 'SPL002',
                'name'          => 'CV Snack Nusantara',
                'phone'         => '081234567802',
                'category_code' => 'SUP002',
            ],
            [
                'kode_supplier' => 'SPL003',
                'name'          => 'CV Bersih Sejahtera',
                'phone'         => '081234567803',
                'category_code' => 'SUP003',
            ],
            [
                'kode_supplier' => 'SPL004',
                'name'          => 'PT Minuman Segar Indonesia',
                'phone'         => '081234567804',
                'category_code' => 'SUP004',
            ],
        ];

        foreach ($suppliers as $supplierData) {
            $category = Category::where(
                'kode_kategori',
                $supplierData['category_code']
            )->firstOrFail();

            Supplier::updateOrCreate(
                [
                    'kode_supplier' => $supplierData['kode_supplier'],
                ],
                [
                    'name'        => $supplierData['name'],
                    'phone'       => $supplierData['phone'],
                    'category_id' => $category->id,
                ]
            );
        }

        $this->command->info('✅ SupplierSeeder: 4 supplier berhasil dibuat.');
    }
}