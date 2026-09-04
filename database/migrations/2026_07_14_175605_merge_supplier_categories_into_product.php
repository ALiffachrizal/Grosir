<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    public function up(): void
    {
        DB::transaction(function () {
            $supplierCategories = DB::table('categories')
                ->where('type', 'supplier')
                ->get();

            foreach ($supplierCategories as $supplierCategory) {
                $matchingProductCategory = DB::table('categories')
                    ->where('type', 'product')
                    ->whereRaw('UPPER(name) = ?', [strtoupper($supplierCategory->name)])
                    ->first();

                if ($matchingProductCategory) {
                    DB::table('suppliers')
                        ->where('category_id', $supplierCategory->id)
                        ->update(['category_id' => $matchingProductCategory->id]);

                    DB::table('categories')
                        ->where('id', $supplierCategory->id)
                        ->delete();
                } else {
                    
                    DB::table('categories')
                        ->where('id', $supplierCategory->id)
                        ->update(['type' => 'product']);
                }
            }
        });
    }

    /**
     * Migration ini TIDAK sepenuhnya reversible karena bersifat merge
     * (beberapa baris kategori supplier lama sudah dihapus permanen).
     * Method down() sengaja dikosongkan untuk mencegah rollback yang
     * memberi kesan data bisa kembali seperti semula padahal tidak bisa.
     *
     * Jika benar-benar perlu mundur, kembalikan dari backup database
     * yang dibuat sebelum migration ini dijalankan.
     */
    public function down(): void
    {
        // Sengaja dikosongkan — lihat catatan di atas.
    }
};