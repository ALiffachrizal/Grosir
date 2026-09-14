<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('username', 255);
            $table->foreign('username')
                  ->references('username')
                  ->on('users')
                  ->restrictOnDelete();
            $table->date('date');
            $table->decimal('total_price', 15, 2)->default(0);
            $table->enum('payment_method', ['cash', 'transfer']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};