<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Exports\StockExport;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Menampilkan laporan stok.
     */
    public function stock()
    {
        $products = Product::with('category')
            ->orderByRaw('stock <= minimum_stock DESC')
            ->orderBy('stock')
            ->orderBy('name')
            ->get();

        $lowStockCount = $products
            ->filter(function ($product) {
                return $product->stok_menipis;
            })
            ->count();

        $productCategories = Category::product()
            ->orderBy('name')
            ->get();

        return view('reports.stock', compact(
            'products',
            'lowStockCount',
            'productCategories'
        ));
    }

    /**
     * Mengekspor laporan stok ke Excel.
     */
    public function exportStockExcel()
    {
        $filename = 'laporan-stok-' .
            Carbon::now()->format('Y-m-d') .
            '.xlsx';

        return Excel::download(
            new StockExport(),
            $filename
        );
    }

    /**
     * Menampilkan laporan penjualan.
     */
    public function sales(Request $request)
    {
        $validated = $this->validateSalesReportRequest(
            $request
        );

        [
            $filter,
            $dateFrom,
            $dateTo,
        ] = $this->resolveSalesDateRange($validated);

        $sales = $this->getSales(
            $dateFrom,
            $dateTo
        );

        $summary = $this->calculateSalesSummary(
            $sales
        );

        return view('reports.sales', array_merge(
            [
                'sales' => $sales,
                'filter' => $filter,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ],
            $summary
        ));
    }

    /**
     * Membuka laporan penjualan PDF di browser.
     */
    public function exportSalesPdf(Request $request)
    {
        $validated = $this->validateSalesReportRequest(
            $request
        );

        [
            $filter,
            $dateFrom,
            $dateTo,
        ] = $this->resolveSalesDateRange($validated);

        $sales = $this->getSales(
            $dateFrom,
            $dateTo
        );

        $summary = $this->calculateSalesSummary(
            $sales
        );

        $pdf = Pdf::loadView(
            'reports.sales-pdf',
            array_merge(
                [
                    'sales' => $sales,
                    'filter' => $filter,
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,
                ],
                $summary
            )
        )->setPaper('a4', 'landscape');

        $filename =
            'laporan-penjualan-' .
            $dateFrom->format('Y-m-d') .
            '-sd-' .
            $dateTo->format('Y-m-d') .
            '.pdf';

        /*
        |--------------------------------------------------------------------------
        | PDF dibuka terlebih dahulu di browser
        |--------------------------------------------------------------------------
        */
        return $pdf->stream($filename);
    }

    /**
     * Mengekspor laporan penjualan ke Excel.
     */
    public function exportSalesExcel(Request $request)
    {
        $validated = $this->validateSalesReportRequest(
            $request
        );

        [
            $filter,
            $dateFrom,
            $dateTo,
        ] = $this->resolveSalesDateRange($validated);

        $filename =
            'laporan-penjualan-' .
            $dateFrom->format('Y-m-d') .
            '-sd-' .
            $dateTo->format('Y-m-d') .
            '.xlsx';

        return Excel::download(
            new SalesExport($dateFrom, $dateTo),
            $filename
        );
    }

    /**
     * Memvalidasi filter dan tanggal laporan penjualan.
     */
    private function validateSalesReportRequest(
        Request $request
    ): array {
        return $request->validate([
            'filter' => [
                'nullable',
                Rule::in([
                    'today',
                    'this_month',
                    'this_year',
                    'custom',
                ]),
            ],

            'date_from' => [
                'required_if:filter,custom',
                'nullable',
                'date_format:Y-m-d',
            ],

            'date_to' => [
                'required_if:filter,custom',
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:date_from',
            ],
        ], [
            'filter.in' =>
                'Filter periode laporan tidak valid.',

            'date_from.required_if' =>
                'Tanggal awal wajib diisi untuk rentang manual.',

            'date_from.date_format' =>
                'Format tanggal awal tidak valid.',

            'date_to.required_if' =>
                'Tanggal akhir wajib diisi untuk rentang manual.',

            'date_to.date_format' =>
                'Format tanggal akhir tidak valid.',

            'date_to.after_or_equal' =>
                'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ]);
    }

    /**
     * Menentukan tanggal awal dan akhir berdasarkan filter.
     */
    private function resolveSalesDateRange(
        array $validated
    ): array {
        $filter = $validated['filter']
            ?? 'this_month';

        switch ($filter) {
            case 'today':
                $dateFrom = Carbon::today();
                $dateTo = Carbon::today();
                break;

            case 'this_year':
                $dateFrom = Carbon::now()
                    ->startOfYear();

                $dateTo = Carbon::now()
                    ->endOfYear();
                break;

            case 'custom':
                $dateFrom = Carbon::createFromFormat(
                    'Y-m-d',
                    $validated['date_from']
                )->startOfDay();

                $dateTo = Carbon::createFromFormat(
                    'Y-m-d',
                    $validated['date_to']
                )->endOfDay();
                break;

            case 'this_month':
            default:
                $filter = 'this_month';

                $dateFrom = Carbon::now()
                    ->startOfMonth();

                $dateTo = Carbon::now()
                    ->endOfMonth();
                break;
        }

        return [
            $filter,
            $dateFrom,
            $dateTo,
        ];
    }

    /**
     * Mengambil penjualan berdasarkan periode laporan.
     */
    private function getSales(
        Carbon $dateFrom,
        Carbon $dateTo
    ): Collection {
        return Sale::with([
            'details.product.category',
            'user',
            'refunds.product.category',
        ])
            ->whereBetween('date', [
                $dateFrom->toDateString(),
                $dateTo->toDateString(),
            ])
            ->latest()
            ->get();
    }

    /**
     * Menghitung seluruh ringkasan laporan penjualan.
     */
    private function calculateSalesSummary(
        Collection $sales
    ): array {
        $totalSales = (float) $sales->sum(
            'total_price'
        );

        $totalRefundNominal = 0;
        $totalRefundQty = 0;
        $totalRefunds = 0;

        foreach ($sales as $sale) {
            $saleRefundNominal =
                $this->calculateSaleRefundNominal($sale);

            $saleRefundQuantity = (int) $sale
                ->refunds
                ->sum('quantity');

            /*
            |--------------------------------------------------------------------------
            | Simpan hasil per transaksi
            |--------------------------------------------------------------------------
            |
            | Nilai ini nanti dapat langsung digunakan oleh view web dan PDF,
            | sehingga view tidak perlu menghitung refund berulang kali.
            |
            */
            $sale->setAttribute(
                'refund_nominal',
                $saleRefundNominal
            );

            $sale->setAttribute(
                'refund_quantity',
                $saleRefundQuantity
            );

            $sale->setAttribute(
                'net_revenue',
                (float) $sale->total_price -
                    $saleRefundNominal
            );

            $totalRefundNominal +=
                $saleRefundNominal;

            $totalRefundQty +=
                $saleRefundQuantity;

            /*
            |--------------------------------------------------------------------------
            | Hitung transaksi refund secara unik
            |--------------------------------------------------------------------------
            |
            | Satu transaksi yang memiliki beberapa record refund tetap
            | dihitung sebagai satu transaksi refund.
            |
            */
            if ($sale->refunds->isNotEmpty()) {
                $totalRefunds++;
            }
        }

        $netRevenue =
            $totalSales - $totalRefundNominal;

        return [
            'totalSales' => $totalSales,

            /*
             * Jumlah transaksi penjualan yang memiliki refund.
             */
            'totalRefunds' => $totalRefunds,

            /*
             * Total jumlah unit barang yang direfund.
             */
            'totalRefundQty' => $totalRefundQty,

            /*
             * Total nilai rupiah barang yang direfund.
             */
            'totalRefundNominal' =>
                $totalRefundNominal,

            /*
             * Penjualan kotor dikurangi nominal refund.
             */
            'netRevenue' => $netRevenue,
        ];
    }

    /**
     * Menghitung nominal refund pada satu transaksi.
     */
    private function calculateSaleRefundNominal(
        Sale $sale
    ): float {
        $refundNominal = 0;

        foreach ($sale->refunds as $refund) {
            /*
            |--------------------------------------------------------------------------
            | Cari harga produk pada transaksi aslinya
            |--------------------------------------------------------------------------
            |
            | Harga refund harus menggunakan harga saat transaksi penjualan,
            | bukan harga produk terbaru pada tabel products.
            |
            */
            $saleDetail = $sale->details
                ->firstWhere(
                    'kode_produk',
                    $refund->kode_produk
                );

            if (!$saleDetail) {
                continue;
            }

            $refundNominal +=
                (int) $refund->quantity
                * (float) $saleDetail->unit_price;
        }

        return $refundNominal;
    }
}