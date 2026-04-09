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
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->restrictOnDelete();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            // in = masuk (penerimaan), out = keluar (penjualan), refund = retur
            $table->enum('type', ['in', 'out', 'refund']);

            $table->integer('quantity');

            // Referensi ke transaksi terkait
            // Contoh: reference_type="purchase_order", reference_id=5
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