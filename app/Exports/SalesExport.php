<?php

namespace App\Exports;

use App\Models\Sale;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected $dateFrom;
    protected $dateTo;
    protected $sales;

    public function __construct($dateFrom, $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo   = $dateTo;
        $this->sales    = Sale::with(['details.product', 'user', 'refunds'])
            ->whereBetween('date', [
                Carbon::parse($dateFrom)->toDateString(),
                Carbon::parse($dateTo)->toDateString(),
            ])
            ->latest()
            ->get();
    }

    public function collection()
    {
        return $this->sales;
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Transaksi',
            'Tanggal',
            'Produk',
            'Metode Pembayaran',
            'Total (Rp)',
            'Refund (Unit)',
            'Kasir',
        ];
    }

    public function map($sale): array
    {
        static $no = 0;
        $no++;

        $products = $sale->details->map(fn($d) =>
            $d->product->name . ' (' . $d->quantity . ')'
        )->implode(', ');

        return [
            $no,
            '#' . str_pad($sale->id, 6, '0', STR_PAD_LEFT),
            Carbon::parse($sale->date)->format('d/m/Y'),
            $products,
            $sale->payment_method_label,
            $sale->total_price,
            $sale->refunds->sum('quantity'),
            $sale->user->username,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header bold & background
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F2937']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    public function title(): string
    {
        return 'Laporan Penjualan';
    }
}