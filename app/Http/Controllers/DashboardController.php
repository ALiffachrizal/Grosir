<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\Supplier;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts  = Product::count();
        $totalSuppliers = Supplier::count();
        $pendingOrders  = PurchaseOrder::where('status', 'pending')->count();
        $todaySales     = Sale::whereDate('date', Carbon::today())->sum('total_price');

        [$salesLabels, $salesChart] = $this->getSalesChartData();

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

    /**
     * Ambil data chart penjualan 7 hari terakhir.
     *
     * @return array{0: array<int, string>, 1: array<int, float>} [labels, values]
     */
    private function getSalesChartData(): array
    {
        $startDate = Carbon::today()->subDays(6);
        $endDate   = Carbon::today();

        // Satu query: total penjualan per tanggal, dalam rentang 7 hari
        $salesByDate = Sale::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('date, SUM(total_price) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $labels = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $key  = $date->format('Y-m-d');

            $labels[] = $date->locale('id')->isoFormat('D MMM');
            $values[] = (float) ($salesByDate[$key] ?? 0);
        }

        return [$labels, $values];
    }
}