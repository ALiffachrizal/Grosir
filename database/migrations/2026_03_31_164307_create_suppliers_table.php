<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->string('kode_supplier', 10)->primary();
            $table->string('name');
            $table->string('phone')->nullable();

            $table->string('kode_kategori', 10);
            $table->foreign('kode_kategori')
                ->references('kode_kategori')
                ->on('categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};