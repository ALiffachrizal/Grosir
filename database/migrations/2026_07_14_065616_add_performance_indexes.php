<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('stock_logs', function (Blueprint $table) {
            $table->index(['reference_type', 'reference_id'], 'stock_logs_reference_index');
        });

        Schema::table('sale_details', function (Blueprint $table) {
            $table->index(['sale_id', 'kode_produk'], 'sale_details_sale_produk_index');
        });

        // sales: dashboard & report sering filter/group by tanggal
        Schema::table('sales', function (Blueprint $table) {
            $table->index('date', 'sales_date_index');
        });

        // purchase_orders: halaman receiving filter by status terus-menerus
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index('status', 'purchase_orders_status_index');
        });


        Schema::table('products', function (Blueprint $table) {
            $table->index(['stock', 'minimum_stock'], 'products_stock_minimum_index');
        });
    }

    public function down(): void
    {
        Schema::table('stock_logs', function (Blueprint $table) {
            $table->dropIndex('stock_logs_reference_index');
        });

        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropIndex('sale_details_sale_produk_index');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_date_index');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex('purchase_orders_status_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_stock_minimum_index');
        });
    }
};