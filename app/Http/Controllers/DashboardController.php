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
     * Sebelumnya method ini melakukan 7 query terpisah (satu query per hari
     * dalam loop). Sekarang hanya 1 query dengan GROUP BY, hasilnya di-map
     * ke array 7 hari di PHP (bukan di database) supaya hari yang tidak ada
     * transaksi tetap muncul sebagai 0, bukan hilang dari chart.
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
        // Hasilnya: ['2026-06-30' => 150000, '2026-07-02' => 75000, ...]
        // (tanggal tanpa transaksi tidak akan muncul di sini)

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