<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SaleDetail;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // ===== STAT CARDS =====

        // Total produk
        $totalProducts = Product::count();

        // Total supplier
        $totalSuppliers = Supplier::count();

        // Pending orders
        $pendingOrders = PurchaseOrder::where('status', 'pending')->count();

        // Penjualan hari ini
        $todaySales = Sale::whereDate('date', Carbon::today())->sum('total_price');

        // ===== GRAFIK 7 HARI TERAKHIR =====
        $salesChart = [];
        $salesLabels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $salesLabels[] = $date->locale('id')->isoFormat('D MMM');
            $salesChart[] = Sale::whereDate('date', $date)->sum('total_price');
        }

        // ===== STOK MENIPIS =====
        $lowStockProducts = Product::whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock')
            ->take(10)
            ->get();

        return view('dashboard', compact(
            'totalProducts',
            'totalSuppliers',
            'pendingOrders',
            'todaySales',
            'salesChart',
            'salesLabels',
            'lowStockProducts',
        ));
    }
}