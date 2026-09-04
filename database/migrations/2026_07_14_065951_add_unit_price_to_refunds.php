<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 2)
                ->nullable()
                ->after('quantity');
        });

        
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