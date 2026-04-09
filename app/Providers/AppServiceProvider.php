<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Product;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // View Composer — hitung produk stok menipis
        // Dijalankan setiap kali layout 'app' dirender
        View::composer('layouts.app', function ($view) {
            $lowStockCount = 0;

            if (auth()->check()) {
                $lowStockCount = Product::whereColumn('stock', '<=', 'minimum_stock')->count();
            }

            $view->with('lowStockCount', $lowStockCount);
        });
    }
}