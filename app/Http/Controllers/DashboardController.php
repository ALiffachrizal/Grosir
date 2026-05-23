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

    $totalProducts  = Product::count();
    $totalSuppliers = Supplier::count();
    $pendingOrders  = PurchaseOrder::where('status', 'pending')->count();
    $todaySales     = Sale::whereDate('date', Carbon::today())->sum('total_price');

    $salesChart  = [];
    $salesLabels = [];

    for ($i = 6; $i >= 0; $i--) {
        $date          = Carbon::today()->subDays($i);
        $salesLabels[] = $date->locale('id')->isoFormat('D MMM');
        $salesChart[]  = Sale::whereDate('date', $date)->sum('total_price');
    }

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