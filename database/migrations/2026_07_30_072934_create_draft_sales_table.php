<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('draft_sales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            
            $table->string('note', 255)->nullable();

            $table->timestamps();
        });

        Schema::create('draft_sale_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('draft_sale_id')
                ->constrained('draft_sales')
                ->cascadeOnDelete();

            $table->string('kode_produk', 10);
            $table->foreign('kode_produk')
                ->references('kode_produk')
                ->on('products')
                ->restrictOnDelete();

            $table->integer('quantity');

         
            $table->decimal('unit_price', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draft_sale_details');
        Schema::dropIfExists('draft_sales');
    }
};