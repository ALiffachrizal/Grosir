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
     * Mengambil data penjualan sesuai periode laporan.
     */
    public function collection(): Collection
    {
        $this->number = 0;

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
     * Judul setiap kolom Excel.
     */
    public function headings(): array
    {
        return [
            'No',
            'No. Transaksi',
            'Tanggal',
            'Produk',
            'Metode Pembayaran',
            'Penjualan Kotor (Rp)',
            'Nominal Refund (Rp)',
            'Penjualan Bersih (Rp)',
            'Jumlah Unit Refund',
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
        |
        | Setiap produk ditampilkan pada baris baru di dalam satu sel Excel.
        |
        */
        $products = $sale->details
            ->map(function ($detail) {
                $productName = $detail->product->name
                    ?? $detail->kode_produk;

                $baseUnit = $detail->product->base_unit
                    ?? 'unit';

                $description = trim(
                    (string) $detail->description
                );

                $text = $productName
                    . ' ('
                    . $detail->quantity
                    . ' '
                    . $baseUnit
                    . ')';

                if ($description !== '') {
                    $text .= ' - ' . $description;
                }

                return $text;
            })
            ->implode("\n");

        /*
        |--------------------------------------------------------------------------
        | Nominal refund
        |--------------------------------------------------------------------------
        */
        $refundNominal = $this->calculateRefundNominal(
            $sale
        );

        /*
        |--------------------------------------------------------------------------
        | Jumlah unit refund
        |--------------------------------------------------------------------------
        */
        $refundQuantity = (int) $sale->refunds
            ->sum('quantity');

        /*
        |--------------------------------------------------------------------------
        | Penjualan bersih
        |--------------------------------------------------------------------------
        */
        $netRevenue = (float) $sale->total_price
            - $refundNominal;

        return [
            $this->number,

            '#' . str_pad(
                (string) $sale->id,
                6,
                '0',
                STR_PAD_LEFT
            ),

            Carbon::parse($sale->date)
                ->format('d/m/Y'),

            $products,

            $sale->payment_method_label,

            (float) $sale->total_price,

            $refundNominal,

            $netRevenue,

            $refundQuantity,

            $sale->user->username ?? '-',
        ];
    }

    /**
     * Menghitung nominal refund pada satu transaksi.
     *
     * Harga refund menggunakan harga ketika transaksi terjadi,
     * bukan harga produk terbaru pada tabel products.
     */
    private function calculateRefundNominal(
        Sale $sale
    ): float {
        $refundNominal = 0;

        foreach ($sale->refunds as $refund) {
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

    /**
     * Format angka pada kolom Excel.
     */
    public function columnFormats(): array
    {
        return [
            'F' => '"Rp " #,##0',
            'G' => '"Rp " #,##0',
            'H' => '"Rp " #,##0',
            'I' => '#,##0',
        ];
    }

    /**
     * Style baris heading.
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
                    'horizontal' =>
                        Alignment::HORIZONTAL_CENTER,

                    'vertical' =>
                        Alignment::VERTICAL_CENTER,
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
     * Mengatur total, tampilan tabel, dan pengaturan cetak.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                /*
                |--------------------------------------------------------------------------
                | Posisi baris
                |--------------------------------------------------------------------------
                |
                | Baris pertama adalah heading. Jika tidak ada transaksi,
                | total tetap ditulis pada baris kedua.
                |
                */
                $lastDataRow = max(
                    1,
                    $sheet->getHighestRow()
                );

                $totalRow = $lastDataRow + 1;

                /*
                |--------------------------------------------------------------------------
                | Ringkasan keseluruhan
                |--------------------------------------------------------------------------
                */
                $totalGrossSales = (float) $this->sales
                    ->sum('total_price');

                $totalRefundNominal = (float) $this->sales
                    ->sum(function (Sale $sale) {
                        return $this->calculateRefundNominal(
                            $sale
                        );
                    });

                $totalNetRevenue = $totalGrossSales
                    - $totalRefundNominal;

                $totalRefundQuantity = (int) $this->sales
                    ->sum(function (Sale $sale) {
                        return $sale->refunds
                            ->sum('quantity');
                    });

                /*
                |--------------------------------------------------------------------------
                | Jumlah transaksi refund
                |--------------------------------------------------------------------------
                |
                | Satu transaksi yang memiliki beberapa record refund
                | tetap dihitung sebagai satu transaksi refund.
                |
                */
                $totalRefundTransactions = $this->sales
                    ->filter(function (Sale $sale) {
                        return $sale->refunds->isNotEmpty();
                    })
                    ->count();

                /*
                |--------------------------------------------------------------------------
                | Baris total
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
                    $totalGrossSales
                );

                $sheet->setCellValue(
                    "G{$totalRow}",
                    $totalRefundNominal
                );

                $sheet->setCellValue(
                    "H{$totalRow}",
                    $totalNetRevenue
                );

                $sheet->setCellValue(
                    "I{$totalRow}",
                    $totalRefundQuantity
                );

                $sheet->setCellValue(
                    "J{$totalRow}",
                    $totalRefundTransactions
                        . ' transaksi refund'
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
                            'borderStyle' =>
                                Border::BORDER_MEDIUM,

                            'color' => [
                                'argb' => 'FF1E293B',
                            ],
                        ],
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | Warna nominal refund
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle("G{$totalRow}")
                    ->getFont()
                    ->getColor()
                    ->setARGB('FFEA580C');

                /*
                |--------------------------------------------------------------------------
                | Warna penjualan bersih
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle("H{$totalRow}")
                    ->getFont()
                    ->getColor()
                    ->setARGB('FF15803D');

                /*
                |--------------------------------------------------------------------------
                | Format angka
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
                    ->setRowHeight(30);

                $sheet->getRowDimension($totalRow)
                    ->setRowHeight(27);

                if ($lastDataRow >= 2) {
                    for (
                        $row = 2;
                        $row <= $lastDataRow;
                        $row++
                    ) {
                        $sheet->getRowDimension($row)
                            ->setRowHeight(34);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Lebar kolom
                |--------------------------------------------------------------------------
                */
                $columnWidths = [
                    'A' => 7,
                    'B' => 17,
                    'C' => 15,
                    'D' => 42,
                    'E' => 20,
                    'F' => 21,
                    'G' => 21,
                    'H' => 22,
                    'I' => 20,
                    'J' => 20,
                ];

                foreach (
                    $columnWidths as $column => $width
                ) {
                    $sheet->getColumnDimension($column)
                        ->setWidth($width);
                }

                /*
                |--------------------------------------------------------------------------
                | Alignment umum
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle("A1:J{$totalRow}")
                    ->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                $sheet->getStyle("A2:A{$totalRow}")
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

                $sheet->getStyle("J{$totalRow}")
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                /*
                |--------------------------------------------------------------------------
                | Style khusus baris data
                |--------------------------------------------------------------------------
                |
                | Pemeriksaan ini mencegah range seperti C2:C1 ketika
                | laporan tidak mempunyai transaksi.
                |
                */
                if ($lastDataRow >= 2) {
                    $sheet->getStyle(
                        "C2:C{$lastDataRow}"
                    )
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_CENTER
                        );

                    $sheet->getStyle(
                        "E2:E{$lastDataRow}"
                    )
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_CENTER
                        );

                    $sheet->getStyle(
                        "D2:D{$lastDataRow}"
                    )
                        ->getAlignment()
                        ->setWrapText(true);

                    $sheet->getStyle(
                        "J2:J{$lastDataRow}"
                    )
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_LEFT
                        );
                }

                /*
                |--------------------------------------------------------------------------
                | Filter dan heading tetap terlihat
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

                $pageSetup->setFitToPage(true);
                $pageSetup->setFitToWidth(1);
                $pageSetup->setFitToHeight(0);

                /*
                 * Ulangi heading jika dicetak lebih dari satu halaman.
                 */
                $pageSetup
                    ->setRowsToRepeatAtTopByStartAndEnd(
                        1,
                        1
                    );

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