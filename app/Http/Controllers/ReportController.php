<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Refund;
use App\Models\Product;
use App\Exports\SalesExport;
use App\Exports\StockExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Laporan Stok
     */
    public function stock()
    {
        $products = Product::orderByRaw('stock <= minimum_stock DESC')
            ->orderBy('stock')
            ->orderBy('name')
            ->get();

        $lowStockCount = $products->filter(fn($p) => $p->stok_menipis)->count();

        return view('reports.stock', compact('products', 'lowStockCount'));
    }

    /**
     * Export Stok Excel
     */
    public function exportStockExcel()
    {
        $filename = 'laporan-stok-' . Carbon::now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new StockExport, $filename);
    }

    /**
     * Laporan Penjualan
     */
    public function sales(Request $request)
    {
        $filter   = $request->filter ?? 'this_month';
        $dateFrom = null;
        $dateTo   = null;

        switch ($filter) {
            case 'today':
                $dateFrom = Carbon::today();
                $dateTo   = Carbon::today();
                break;
            case 'this_month':
                $dateFrom = Carbon::now()->startOfMonth();
                $dateTo   = Carbon::now()->endOfMonth();
                break;
            case 'this_year':
                $dateFrom = Carbon::now()->startOfYear();
                $dateTo   = Carbon::now()->endOfYear();
                break;
            case 'custom':
                $dateFrom = $request->date_from
                    ? Carbon::parse($request->date_from)
                    : Carbon::now()->startOfMonth();
                $dateTo = $request->date_to
                    ? Carbon::parse($request->date_to)
                    : Carbon::now()->endOfMonth();
                break;
        }

        $sales = Sale::with(['details.product', 'user', 'refunds'])
            ->whereBetween('date', [
                $dateFrom->toDateString(),
                $dateTo->toDateString(),
            ])
            ->latest()
            ->get();

        $totalSales     = $sales->sum('total_price');
        $totalRefunds   = Refund::whereHas('sale', function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()]);
        })->count();
        $totalRefundQty = Refund::whereHas('sale', function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()]);
        })->sum('quantity');
        $netRevenue     = $totalSales;

        return view('reports.sales', compact(
            'sales', 'totalSales', 'totalRefunds',
            'totalRefundQty', 'netRevenue',
            'filter', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Export Penjualan PDF
     */
    public function exportSalesPdf(Request $request)
    {
        $filter   = $request->filter ?? 'this_month';
        $dateFrom = $this->getDateFrom($filter, $request->date_from);
        $dateTo   = $this->getDateTo($filter, $request->date_to);

        $sales = Sale::with(['details.product', 'user', 'refunds'])
            ->whereBetween('date', [
                $dateFrom->toDateString(),
                $dateTo->toDateString(),
            ])
            ->latest()
            ->get();

        $totalSales     = $sales->sum('total_price');
        $totalRefunds   = Refund::whereHas('sale', function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()]);
        })->count();
        $totalRefundQty = Refund::whereHas('sale', function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()]);
        })->sum('quantity');
        $netRevenue     = $totalSales;

        $pdf = Pdf::loadView('reports.sales-pdf', compact(
            'sales', 'totalSales', 'totalRefunds',
            'totalRefundQty', 'netRevenue',
            'dateFrom', 'dateTo'
        ))->setPaper('a4', 'landscape');

        $filename = 'laporan-penjualan-' . $dateFrom->format('Y-m-d') . '-sd-' . $dateTo->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export Penjualan Excel
     */
    public function exportSalesExcel(Request $request)
    {
        $filter   = $request->filter ?? 'this_month';
        $dateFrom = $this->getDateFrom($filter, $request->date_from);
        $dateTo   = $this->getDateTo($filter, $request->date_to);

        $filename = 'laporan-penjualan-' . $dateFrom->format('Y-m-d') . '-sd-' . $dateTo->format('Y-m-d') . '.xlsx';

        return Excel::download(new SalesExport($dateFrom, $dateTo), $filename);
    }

    /**
     * Helper: get date from
     */
    private function getDateFrom($filter, $customDate = null): Carbon
    {
        return match($filter) {
            'today'      => Carbon::today(),
            'this_month' => Carbon::now()->startOfMonth(),
            'this_year'  => Carbon::now()->startOfYear(),
            'custom'     => $customDate ? Carbon::parse($customDate) : Carbon::now()->startOfMonth(),
            default      => Carbon::now()->startOfMonth(),
        };
    }

    /**
     * Helper: get date to
     */
    private function getDateTo($filter, $customDate = null): Carbon
    {
        return match($filter) {
            'today'      => Carbon::today(),
            'this_month' => Carbon::now()->endOfMonth(),
            'this_year'  => Carbon::now()->endOfYear(),
            'custom'     => $customDate ? Carbon::parse($customDate) : Carbon::now()->endOfMonth(),
            default      => Carbon::now()->endOfMonth(),
        };
    }
}