<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sebelumnya, nominal refund selalu dihitung ulang dengan mencari
     * sale_details yang cocok setiap kali dibutuhkan (lihat
     * ReportController::calculateSaleRefundNominal). Ini rapuh: jika suatu
     * saat sale_details bisa dihapus/diubah secara independen, riwayat
     * nominal refund akan ikut berubah atau hilang.
     *
     * Migration ini menyimpan unit_price LANGSUNG di baris refund saat
     * refund dibuat, sehingga nilainya terkunci selamanya — tidak
     * bergantung pada data lain yang bisa berubah di kemudian hari.
     *
     * Kolom bersifat nullable supaya AMAN dijalankan di database yang
     * sudah berisi data refund lama (baris lama akan di-backfill di bawah,
     * baris baru akan otomatis terisi lewat RefundController).
     */
    public function up(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 2)
                ->nullable()
                ->after('quantity');
        });

        /*
        |----------------------------------------------------------------------
        | Backfill data refund yang sudah ada
        |----------------------------------------------------------------------
        | Ambil unit_price dari sale_details yang cocok (sale_id + kode_produk)
        | untuk setiap refund yang belum punya unit_price.
        |
        | Query native MySQL dipakai karena UPDATE ... JOIN tidak didukung
        | langsung oleh query builder Eloquent.
        */
        DB::statement(<<<SQL
            UPDATE refunds AS r
            INNER JOIN sale_details AS sd
                ON sd.sale_id = r.sale_id
                AND sd.kode_produk = r.kode_produk
            SET r.unit_price = sd.unit_price
            WHERE r.unit_price IS NULL
        SQL);
    }

    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn('unit_price');
        });
    }
};