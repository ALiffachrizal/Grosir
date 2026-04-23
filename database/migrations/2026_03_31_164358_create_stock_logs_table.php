<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_produk', 10);
            $table->foreign('kode_produk')
                  ->references('kode_produk')
                  ->on('products')
                  ->restrictOnDelete();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->enum('type', ['in', 'out', 'refund']);
            $table->integer('quantity');
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_logs');
    }
};  