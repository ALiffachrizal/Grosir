<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    public function collection()
    {
        return Product::orderByRaw('stock <= minimum_stock DESC')
            ->orderBy('stock')
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Produk',
            'Kategori',
            'Satuan',
            'Stok',
            'Min. Stok',
            'Harga Beli (Rp)',
            'Harga Jual (Rp)',
            'Status',
        ];
    }

    public function map($product): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $product->name,
            $product->category,
            $product->base_unit,
            $product->stock,
            $product->minimum_stock,
            $product->purchase_price,
            $product->selling_price,
            $product->stok_menipis ? 'Menipis' : 'Aman',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F2937']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    public function title(): string
    {
        return 'Laporan Stok';
    }
}