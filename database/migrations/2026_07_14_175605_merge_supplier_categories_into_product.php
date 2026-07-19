<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migration ini menggabungkan kategori supplier ke dalam kategori
     * produk, supaya supplier & produk terhubung lewat category_id yang
     * SAMA PERSIS di database — bukan lewat kecocokan nama sebagai teks.
     *
     * Sebelumnya, kategori produk dan kategori supplier adalah 2 baris
     * data yang TERPISAH di tabel categories (dibedakan lewat kolom type),
     * meskipun sengaja dibuat dengan nama yang sama oleh admin. Fitur
     * "buat Purchase Order" mencocokkan produk ke supplier dengan
     * membandingkan NAMA kategori sebagai teks — rawan salah kalau ada
     * perbedaan huruf besar/kecil, spasi, atau typo sedikit saja.
     *
     * Untuk setiap kategori bertipe 'supplier':
     *   1. Jika ada kategori bertipe 'product' dengan nama yang sama
     *      (dibandingkan tanpa peduli besar/kecil huruf) → semua supplier
     *      yang memakai kategori lama dipindahkan ke kategori produk itu,
     *      lalu kategori supplier yang lama dihapus (sudah tidak dipakai).
     *   2. Jika TIDAK ada kategori produk yang cocok namanya → kategori
     *      supplier itu diubah tipenya langsung menjadi 'product', supaya
     *      tetap valid dipakai dan datanya tidak hilang.
     */
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
                    // Pindahkan semua supplier ke kategori produk yang cocok
                    DB::table('suppliers')
                        ->where('category_id', $supplierCategory->id)
                        ->update(['category_id' => $matchingProductCategory->id]);

                    // Kategori supplier lama sudah tidak dipakai siapa pun, hapus
                    DB::table('categories')
                        ->where('id', $supplierCategory->id)
                        ->delete();
                } else {
                    // Tidak ada padanan nama di kategori produk —
                    // ubah saja tipe kategori ini jadi 'product'.
                    // Supplier yang memakainya tetap terhubung ke
                    // baris kategori yang sama (id tidak berubah).
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