<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Reset table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Admin
        User::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role'     => 'admin',
        ]);

        // Cashier (Kasir)
        User::create([
            'username' => 'kasir',
            'password' => Hash::make('kasir123'),
            'role'     => 'cashier', // WAJIB sesuai ENUM
        ]);

        //  Warehouse (Opsional, sekalian)
        // User::create([
        //     'username' => 'gudang',
        //     'password' => Hash::make('gudang123'),
        //     'role'     => 'warehouse',
        // ]);

        // Output ke console
        $this->command->info('✅ UserSeeder: admin, cashier, berhasil dibuat.');

        $this->command->table(
            ['Username', 'Password', 'Role'],
            [
                ['admin', 'admin123', 'admin'],
                ['kasir', 'kasir123', 'cashier'],
                // ['gudang', 'gudang123', 'warehouse'],
            ]
        );
    }
}