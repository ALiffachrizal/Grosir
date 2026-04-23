<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_produk', 10)->unique()->nullable();
            $table->string('name');
            $table->string('category', 100);
            $table->string('base_unit', 20);
            $table->integer('items_per_package')->default(1);
            $table->integer('items_per_bundle')->nullable()->default(1);
            $table->integer('stock')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('selling_price', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};