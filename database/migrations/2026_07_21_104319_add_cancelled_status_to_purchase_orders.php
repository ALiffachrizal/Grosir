<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    public function up(): void
    {
        DB::statement(
            "ALTER TABLE purchase_orders MODIFY COLUMN status ENUM('pending', 'received', 'cancelled') NOT NULL DEFAULT 'pending'"
        );
    }

    public function down(): void
    {

        DB::table('purchase_orders')
            ->where('status', 'cancelled')
            ->update(['status' => 'pending']);

        DB::statement(
            "ALTER TABLE purchase_orders MODIFY COLUMN status ENUM('pending', 'received') NOT NULL DEFAULT 'pending'"
        );
    }
};