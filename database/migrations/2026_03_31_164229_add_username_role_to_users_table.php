<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'id')) {
                $table->dropColumn('id');
            }
            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn(['name', 'email', 'email_verified_at']);
            }
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 255)->primary();
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'cashier', 'warehouse'])->default('cashier');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'role']);
            $table->string('name')->after('id');
            $table->string('email')->unique()->after('name');
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });
    }
};