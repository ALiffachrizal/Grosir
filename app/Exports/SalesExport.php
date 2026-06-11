<?php

namespace App\Exports;

use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnFormatting,
    WithEvents,
    WithTitle
{
    protected Carbon $dateFrom;
    protected Carbon $dateTo;
    protected Collection $sales;

    private int $number = 0;

    public function __construct(Carbon $dateFrom, Carbon $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->sales = collect();
    }

    /**
     * Mengambil data penjualan sesuai periode.
     */
    public function collection(): Collection
    {
        $this->sales = Sale::with([
                'details.product',
                'refunds',
                'user',
            ])
            ->whereBetween('date', [
                $this->dateFrom->toDateString(),
                $this->dateTo->toDateString(),
            ])
            ->latest()
            ->get();

        return $this->sales;
    }

    /**
     * Judul kolom Excel.
     */
    public function headings(): array
    {
        return [
            'No',
            'No. Transaksi',
            'Tanggal',
            'Produk',
            'Metode Pembayaran',
            'Total Kotor (Rp)',
            'Refund (Rp)',
            'Total Bersih (Rp)',
            'Refund (Unit)',
            'Kasir',
        ];
    }

    /**
     * Mengatur isi setiap baris transaksi.
     */
    public function map($sale): array
    {
        $this->number++;

        /*
        |--------------------------------------------------------------------------
        | Daftar produk transaksi
        |--------------------------------------------------------------------------
        */
        $products = $sale->details
            ->map(function ($detail) {
                $productName = $detail->product->name
                    ?? $detail->kode_produk;

                $baseUnit = $detail->product->base_unit
                    ?? 'unit';

                return $productName
                    . ' ('
                    . $detail->quantity
                    . ' '
                    . $baseUnit
                    . ')';
            })
            ->implode(', ');

        /*
        |--------------------------------------------------------------------------
        | Hitung nominal refund transaksi
        |--------------------------------------------------------------------------
        */
        $refundNominal = $this->calculateRefundNominal($sale);

        /*
        |--------------------------------------------------------------------------
        | Hitung jumlah unit refund
        |--------------------------------------------------------------------------
        */
        $refundQuantity = (int) $sale->refunds->sum('quantity');

        /*
        |--------------------------------------------------------------------------
        | Total bersih setelah dikurangi refund
        |--------------------------------------------------------------------------
        */
        $totalBersih = (float) $sale->total_price - $refundNominal;

        /*
        |--------------------------------------------------------------------------
        | Nama metode pembayaran
        |--------------------------------------------------------------------------
        */
        $paymentMethod = match ($sale->payment_method) {
            'cash' => 'Tunai',
            'transfer' => 'Transfer',
            default => ucfirst((string) $sale->payment_method),
        };

        return [
            $this->number,

            '#' . str_pad(
                (string) $sale->id,
                6,
                '0',
                STR_PAD_LEFT
            ),

            Carbon::parse($sale->date)->format('d/m/Y'),

            $products,

            $paymentMethod,

            // Penjualan sebelum dikurangi refund
            (float) $sale->total_price,

            // Nominal barang yang direfund
            $refundNominal,

            // Total akhir setelah dikurangi refund
            $totalBersih,

            // Jumlah barang yang direfund
            $refundQuantity,

            $sale->user->username ?? '-',
        ];
    }

    /**
     * Menghitung nominal refund pada satu transaksi.
     */
    private function calculateRefundNominal($sale): float
    {
        $refundNominal = 0;

        foreach ($sale->refunds as $refund) {
            $saleDetail = $sale->details
                ->firstWhere(
                    'kode_produk',
                    $refund->kode_produk
                );

            if ($saleDetail) {
                $refundNominal +=
                    (float) $refund->quantity
                    * (float) $saleDetail->unit_price;
            }
        }

        return $refundNominal;
    }

    /**
     * Format kolom angka.
     */
    public function columnFormats(): array
    {
        return [
            // Total Kotor
            'F' => '"Rp " #,##0',

            // Refund
            'G' => '"Rp " #,##0',

            // Total Bersih
            'H' => '"Rp " #,##0',

            // Refund Unit
            'I' => '#,##0',
        ];
    }

    /**
     * Style baris judul.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => [
                        'argb' => 'FFFFFFFF',
                    ],
                ],

                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FF1E293B',
                    ],
                ],

                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * Nama lembar Excel.
     */
    public function title(): string
    {
        return 'Laporan Penjualan';
    }

    /**
     * Mengatur baris total, tampilan, dan pengaturan print.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                /*
                |--------------------------------------------------------------------------
                | Menentukan posisi baris
                |--------------------------------------------------------------------------
                */
                $lastDataRow = $sheet->getHighestRow();
                $totalRow = $lastDataRow + 1;

                /*
                |--------------------------------------------------------------------------
                | Hitung total penjualan kotor
                |--------------------------------------------------------------------------
                */
                $totalKotor = (float) $this->sales->sum(
                    'total_price'
                );

                /*
                |--------------------------------------------------------------------------
                | Hitung total nominal refund
                |--------------------------------------------------------------------------
                */
                $totalRefundNominal = 0;

                foreach ($this->sales as $sale) {
                    $totalRefundNominal +=
                        $this->calculateRefundNominal($sale);
                }

                /*
                |--------------------------------------------------------------------------
                | Hitung total bersih
                |--------------------------------------------------------------------------
                */
                $totalBersih =
                    $totalKotor - $totalRefundNominal;

                /*
                |--------------------------------------------------------------------------
                | Hitung total unit refund
                |--------------------------------------------------------------------------
                */
                $totalRefundQuantity = (int) $this->sales
                    ->sum(function ($sale) {
                        return $sale->refunds
                            ->sum('quantity');
                    });

                /*
                |--------------------------------------------------------------------------
                | Buat baris total
                |--------------------------------------------------------------------------
                */
                $sheet->mergeCells(
                    "A{$totalRow}:E{$totalRow}"
                );

                $sheet->setCellValue(
                    "A{$totalRow}",
                    'TOTAL KESELURUHAN'
                );

                $sheet->setCellValue(
                    "F{$totalRow}",
                    $totalKotor
                );

                $sheet->setCellValue(
                    "G{$totalRow}",
                    $totalRefundNominal
                );

                $sheet->setCellValue(
                    "H{$totalRow}",
                    $totalBersih
                );

                $sheet->setCellValue(
                    "I{$totalRow}",
                    $totalRefundQuantity
                );

                $sheet->setCellValue(
                    "J{$totalRow}",
                    ''
                );

                /*
                |--------------------------------------------------------------------------
                | Style baris total
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle(
                    "A{$totalRow}:J{$totalRow}"
                )->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => [
                            'argb' => 'FF111827',
                        ],
                    ],

                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => 'FFE2E8F0',
                        ],
                    ],

                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => [
                                'argb' => 'FF1E293B',
                            ],
                        ],
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | Warnai kolom refund pada baris total
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle("G{$totalRow}")
                    ->getFont()
                    ->getColor()
                    ->setARGB('FFEA580C');

                /*
                |--------------------------------------------------------------------------
                | Warnai total bersih pada baris total
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle("H{$totalRow}")
                    ->getFont()
                    ->getColor()
                    ->setARGB('FF15803D');

                /*
                |--------------------------------------------------------------------------
                | Format Rupiah
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle("F2:H{$totalRow}")
                    ->getNumberFormat()
                    ->setFormatCode('"Rp " #,##0');

                $sheet->getStyle("I2:I{$totalRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                /*
                |--------------------------------------------------------------------------
                | Border seluruh tabel
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle(
                    "A1:J{$totalRow}"
                )->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' =>
                                Border::BORDER_THIN,

                            'color' => [
                                'argb' => 'FFD1D5DB',
                            ],
                        ],
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | Tinggi baris
                |--------------------------------------------------------------------------
                */
                $sheet->getRowDimension(1)
                    ->setRowHeight(26);

                $sheet->getRowDimension($totalRow)
                    ->setRowHeight(25);

                /*
                |--------------------------------------------------------------------------
                | Lebar kolom
                |--------------------------------------------------------------------------
                */
                $sheet->getColumnDimension('A')
                    ->setWidth(7);

                $sheet->getColumnDimension('B')
                    ->setWidth(17);

                $sheet->getColumnDimension('C')
                    ->setWidth(15);

                $sheet->getColumnDimension('D')
                    ->setWidth(38);

                $sheet->getColumnDimension('E')
                    ->setWidth(20);

                $sheet->getColumnDimension('F')
                    ->setWidth(19);

                $sheet->getColumnDimension('G')
                    ->setWidth(17);

                $sheet->getColumnDimension('H')
                    ->setWidth(19);

                $sheet->getColumnDimension('I')
                    ->setWidth(16);

                $sheet->getColumnDimension('J')
                    ->setWidth(15);

                /*
                |--------------------------------------------------------------------------
                | Alignment
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle("A2:A{$totalRow}")
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet->getStyle("C2:C{$lastDataRow}")
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet->getStyle("E2:E{$lastDataRow}")
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet->getStyle("F2:I{$totalRow}")
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_RIGHT
                    );

                $sheet->getStyle("A{$totalRow}")
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_RIGHT
                    );

                $sheet->getStyle("A1:J{$totalRow}")
                    ->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                /*
                |--------------------------------------------------------------------------
                | Bungkus teks produk
                |--------------------------------------------------------------------------
                */
                if ($lastDataRow >= 2) {
                    $sheet->getStyle(
                        "D2:D{$lastDataRow}"
                    )
                        ->getAlignment()
                        ->setWrapText(true);
                }

                /*
                |--------------------------------------------------------------------------
                | Filter dan freeze heading
                |--------------------------------------------------------------------------
                */
                $sheet->setAutoFilter(
                    "A1:J{$lastDataRow}"
                );

                $sheet->freezePane('A2');

                /*
                |--------------------------------------------------------------------------
                | Pengaturan cetak Excel
                |--------------------------------------------------------------------------
                */
                $pageSetup = $sheet->getPageSetup();

                $pageSetup->setOrientation(
                    PageSetup::ORIENTATION_LANDSCAPE
                );

                $pageSetup->setPaperSize(
                    PageSetup::PAPERSIZE_A4
                );

                // Pas satu halaman secara horizontal
                $pageSetup->setFitToPage(true);
                $pageSetup->setFitToWidth(1);
                $pageSetup->setFitToHeight(0);

                // Ulangi heading jika lebih dari satu halaman
                $pageSetup
                    ->setRowsToRepeatAtTopByStartAndEnd(
                        1,
                        1
                    );

                // Area yang dicetak
                $pageSetup->setPrintArea(
                    "A1:J{$totalRow}"
                );

                /*
                |--------------------------------------------------------------------------
                | Margin cetak
                |--------------------------------------------------------------------------
                */
                $sheet->getPageMargins()->setTop(0.4);
                $sheet->getPageMargins()->setBottom(0.4);
                $sheet->getPageMargins()->setLeft(0.25);
                $sheet->getPageMargins()->setRight(0.25);

                $pageSetup->setHorizontalCentered(true);
            },
        ];
    }
}