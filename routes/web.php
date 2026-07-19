<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StoreProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReceivingController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\StockLogController;
use App\Http\Controllers\ReportController;

// Halaman publik
Route::get('/', [StoreProfileController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {

    // Dashboard - semua role bisa akses
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ===== ADMIN ONLY =====
    Route::middleware('role:admin')->group(function () {

        // User management
        Route::resource('users', UserController::class)->except(['show']);

        // Master data
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::resource('suppliers', SupplierController::class);
        Route::resource('products', ProductController::class);

        // Laporan penjualan
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/sales/export-pdf', [ReportController::class, 'exportSalesPdf'])->name('reports.sales.pdf');
        Route::get('/reports/sales/export-excel', [ReportController::class, 'exportSalesExcel'])->name('reports.sales.excel');
    });

    // ===== ADMIN & WAREHOUSE =====
    Route::middleware('role:admin,warehouse')->group(function () {

        // Pemesanan & Penerimaan
        Route::resource('purchase-orders', PurchaseOrderController::class)
             ->only(['index', 'create', 'store', 'show']);
        Route::get('/receiving', [ReceivingController::class, 'index'])->name('receiving.index');
        Route::get('/receiving/{purchaseOrder}', [ReceivingController::class, 'show'])->name('receiving.show');
        Route::post('/receiving/{purchaseOrder}/confirm', [ReceivingController::class, 'confirm'])->name('receiving.confirm');

        // Stock log & laporan stok
        Route::get('/stock-logs', [StockLogController::class, 'index'])->name('stock-logs.index');
        Route::get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
        Route::get('/reports/stock/export-excel', [ReportController::class, 'exportStockExcel'])->name('reports.stock.excel');
    });

    // ===== ADMIN & CASHIER =====
    Route::middleware('role:admin,cashier')->group(function () {

        // Penjualan
        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::get('/sales/products/search', [SaleController::class, 'searchProducts'])->name('sales.products.search');
        Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
        Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
        Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');

        // Refund
        Route::get('/refunds', [RefundController::class, 'index'])->name('refunds.index');
        Route::get('/refunds/create', [RefundController::class, 'create'])->name('refunds.create');
        Route::post('/refunds', [RefundController::class, 'store'])->name('refunds.store');
        Route::get('/refunds/{sale}', [RefundController::class, 'show'])->name('refunds.show');
    });

});

require __DIR__.'/auth.php';