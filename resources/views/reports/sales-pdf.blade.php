<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }

        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #1F2937; margin-bottom: 20px; }
        .header h1 { font-size: 20px; font-weight: bold; color: #1F2937; }
        .header p { color: #666; margin-top: 4px; font-size: 11px; }

        .periode { background: #f3f4f6; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; }
        .periode p { font-size: 11px; color: #555; }
        .periode strong { color: #1F2937; }

        .summary { display: flex; gap: 15px; margin-bottom: 20px; }
        .summary-card { flex: 1; padding: 12px; border-radius: 6px; text-align: center; }
        .summary-card.blue { background: #dbeafe; border: 1px solid #93c5fd; }
        .summary-card.orange { background: #ffedd5; border: 1px solid #fdba74; }
        .summary-card.green { background: #dcfce7; border: 1px solid #86efac; }
        .summary-card p { font-size: 10px; color: #666; margin-bottom: 4px; }
        .summary-card h3 { font-size: 16px; font-weight: bold; }
        .summary-card.blue h3 { color: #1d4ed8; }
        .summary-card.orange h3 { color: #c2410c; }
        .summary-card.green h3 { color: #15803d; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead tr { background: #1F2937; color: white; }
        thead th { padding: 8px 10px; text-align: left; font-size: 11px; }
        tbody tr { border-bottom: 1px solid #e5e7eb; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody td { padding: 7px 10px; font-size: 11px; }
        tfoot tr { background: #f3f4f6; font-weight: bold; border-top: 2px solid #1F2937; }
        tfoot td { padding: 8px 10px; font-size: 12px; }

        .badge { padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: bold; }
        .badge-green { background: #dcfce7; color: #15803d; }
        .badge-blue { background: #dbeafe; color: #1d4ed8; }
        .badge-orange { background: #ffedd5; color: #c2410c; }

        .footer { text-align: center; color: #999; font-size: 10px; border-top: 1px solid #e5e7eb; padding-top: 10px; margin-top: 20px; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <h1>🛒 Toko Grosir IJAD</h1>
        <p>Laporan Penjualan</p>
    </div>

    {{-- Periode --}}
    <div class="periode">
        <p>
            Periode: <strong>{{ $dateFrom->locale('id')->isoFormat('D MMMM Y') }}</strong>
            s/d <strong>{{ $dateTo->locale('id')->isoFormat('D MMMM Y') }}</strong>
            &nbsp;|&nbsp;
            Dicetak: <strong>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y HH:mm') }}</strong>
            &nbsp;|&nbsp;
            Oleh: <strong>{{ auth()->user()->username }}</strong>
        </p>
    </div>

    {{-- Summary --}}
    <div class="summary">
        <div class="summary-card blue">
            <p>Total Penjualan</p>
            <h3>Rp {{ number_format($totalSales, 0, ',', '.') }}</h3>
            <p>{{ $sales->count() }} transaksi</p>
        </div>
        <div class="summary-card orange">
            <p>Total Refund</p>
            <h3>{{ $totalRefunds }} transaksi</h3>
            <p>{{ $totalRefundQty }} unit</p>
        </div>
        <div class="summary-card green">
            <p>Net Revenue</p>
            <h3>Rp {{ number_format($netRevenue, 0, ',', '.') }}</h3>
        </div>
    </div>

    {{-- Tabel --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>No. Transaksi</th>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Metode</th>
                <th>Total</th>
                <th>Refund</th>
                <th>Kasir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $index => $sale)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</strong></td>
                <td>{{ \Carbon\Carbon::parse($sale->date)->format('d/m/Y') }}</td>
                <td>
                    @foreach($sale->details as $detail)
                    {{ $detail->product->name }} ({{ $detail->quantity }})@if(!$loop->last), @endif
                    @endforeach
                </td>
                <td>
                    <span class="badge {{ $sale->payment_method === 'cash' ? 'badge-green' : 'badge-blue' }}">
                        {{ $sale->payment_method_label }}
                    </span>
                </td>
                <td><strong>Rp {{ number_format($sale->total_price, 0, ',', '.') }}</strong></td>
                <td>
                    @if($sale->refunds->count() > 0)
                    <span class="badge badge-orange">{{ $sale->refunds->sum('quantity') }} unit</span>
                    @else
                    -
                    @endif
                </td>
                <td>{{ $sale->user->username }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; padding: 20px; color: #999;">
                    Tidak ada transaksi pada periode ini
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($sales->count() > 0)
        <tfoot>
            <tr>
                <td colspan="5" style="text-align:right">Total Keseluruhan:</td>
                <td>Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- Footer --}}
    <div class="footer">
        <p>Toko Grosir IJAD &mdash; Dokumen ini dicetak secara otomatis oleh sistem</p>
    </div>

</body>
</html>