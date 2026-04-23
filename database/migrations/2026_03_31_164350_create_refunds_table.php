<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')
                  ->constrained('sales')
                  ->restrictOnDelete();
            $table->string('kode_produk', 10);
            $table->foreign('kode_produk')
                  ->references('kode_produk')
                  ->on('products')
                  ->restrictOnDelete();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->integer('quantity');
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};