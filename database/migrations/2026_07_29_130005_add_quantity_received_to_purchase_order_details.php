<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom quantity_received — jumlah barang yang BENERAN
     * diterima, yang bisa berbeda dari quantity (jumlah yang dipesan).
     *
     * Kasus nyata: supplier kadang kirim lebih sedikit dari yang dipesan
     * (barang kurang, stok supplier habis, dll). Sebelumnya sistem selalu
     * menambah stok PERSIS sesuai jumlah pesanan, padahal barang yang
     * benar-benar sampai bisa lebih sedikit.
     *
     * Kolom ini NULLABLE — bernilai NULL selama PO masih 'pending'
     * (belum dikonfirmasi), dan otomatis terisi begitu admin mengonfirmasi
     * penerimaan dengan jumlah yang benar-benar diterima.
     */
    public function up(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->integer('quantity_received')
                ->nullable()
                ->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->dropColumn('quantity_received');
        });
    }
};