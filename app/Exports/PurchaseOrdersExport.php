<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PurchaseOrdersExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithTitle
{
    protected Carbon $dateFrom;
    protected Carbon $dateTo;
    protected ?string $status;
    protected ?string $kodeSupplier;
    protected Collection $purchaseOrders;

    private int $number = 0;

    public function __construct(
        Carbon $dateFrom,
        Carbon $dateTo,
        ?string $status = null,
        ?string $kodeSupplier = null
    ) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->status = $status;
        $this->kodeSupplier = $kodeSupplier;
        $this->purchaseOrders = collect();
    }

    public function collection(): Collection
    {
        $this->number = 0;

        $query = PurchaseOrder::with([
            'supplier.category',
            'user',
            'details.product.category',
        ])->whereBetween('order_date', [
            $this->dateFrom->toDateString(),
            $this->dateTo->toDateString(),
        ]);

        if ($this->status && $this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->kodeSupplier) {
            $query->where('kode_supplier', $this->kodeSupplier);
        }

        $this->purchaseOrders = $query
            ->latest('order_date')
            ->latest('id')
            ->get();

        return $this->purchaseOrders;
    }

    public function headings(): array
    {
        return [
            'No',
            'No. PO',
            'Tanggal Order',
            'Supplier',
            'Kategori Supplier',
            'Dibuat Oleh',
            'Daftar Produk & Jumlah',
            'Jumlah Item',
            'Total Kuantitas (Unit)',
            'Estimasi Biaya Beli (Rp)',
            'Status',
        ];
    }

    public function map($po): array
    {
        $this->number++;

        $itemsSummary = $po->details->map(function ($d) {
            $name = $d->product->name ?? $d->kode_produk;
            $unit = $d->product->base_unit ?? 'Unit';
            return "• {$name}: {$d->quantity} {$unit}";
        })->implode("\n");

        $totalQty = $po->details->sum('quantity');
        $totalCost = (float) $po->details->sum(function ($d) {
            return $d->quantity * (float) ($d->product->purchase_price ?? 0);
        });

        return [
            $this->number,
            'PO #' . $po->id,
            $po->order_date ? Carbon::parse($po->order_date)->format('d/m/Y') : '-',
            $po->supplier->name ?? '-',
            $po->supplier->category->name ?? '-',
            $po->user->username ?? '-',
            $itemsSummary ?: '-',
            $po->details->count(),
            $totalQty,
            $totalCost,
            $po->status_label,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => '1F2937'], // gray-800
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);

        // Auto wrap for items list column (Column G)
        $sheet->getStyle('G')->getAlignment()->setWrapText(true);

        // Alignments
        $sheet->getStyle('A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('J')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('K')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Format number / currency for column J
        $lastRow = max(2, $this->number + 1);
        $sheet->getStyle("J2:J{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        return [];
    }

    public function title(): string
    {
        return 'Laporan PO ' . $this->dateFrom->format('d-m-Y');
    }
}
