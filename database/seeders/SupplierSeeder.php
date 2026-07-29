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
                'category_code' => 'SBK001', // SEMBAKO
            ],
            [
                'kode_supplier' => 'SPL002',
                'name'          => 'CV Snack Nusantara',
                'phone'         => '081234567802',
                'category_code' => 'JJN002', // JAJANAN / SNACK
            ],
            [
                'kode_supplier' => 'SPL003',
                'name'          => 'CV Bersih Sejahtera',
                'phone'         => '081234567803',
                'category_code' => 'RMH003', // KEBUTUHAN RUMAH TANGGA
            ],
            [
                'kode_supplier' => 'SPL004',
                'name'          => 'PT Minuman Segar Indonesia',
                'phone'         => '081234567804',
                'category_code' => 'MNM004', // MINUMAN
            ],
            [
                'kode_supplier' => 'SPL005',
                'name'          => 'UD Bumbu Nusantara',
                'phone'         => '081234567805',
                'category_code' => 'BMD005', // BUMBU DAPUR
            ],
            [
                'kode_supplier' => 'SPL006',
                'name'          => 'PT Sinar Perawatan Indonesia',
                'phone'         => '081234567806',
                'category_code' => 'PRT006', // PERAWATAN TUBUH
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

        $this->command->info('✅ SupplierSeeder: 6 supplier berhasil dibuat.');
    }
}