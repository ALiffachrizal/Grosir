<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('promo_min_qty')->default(0)->after('selling_price');
            $table->unsignedInteger('promo_bonus_qty')->default(0)->after('promo_min_qty');
        });

        Schema::table('sale_details', function (Blueprint $table) {
            $table->unsignedInteger('bonus_quantity')->default(0)->after('quantity');
        });

        Schema::table('draft_sale_details', function (Blueprint $table) {
            $table->unsignedInteger('bonus_quantity')->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('draft_sale_details', function (Blueprint $table) {
            $table->dropColumn('bonus_quantity');
        });

        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropColumn('bonus_quantity');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['promo_min_qty', 'promo_bonus_qty']);
        });
    }
};
