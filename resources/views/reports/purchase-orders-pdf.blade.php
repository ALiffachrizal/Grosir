<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Purchase Order Toko Grosir IJAD</title>
    <style>
        @page {
            margin: 22px 28px 28px 28px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1f2937;
            background: #ffffff;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .header-table {
            width: 100%;
            margin-bottom: 12px;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 8px;
        }

        .brand-name {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }

        .brand-name .highlight {
            color: #eab308;
        }

        .brand-description {
            margin-top: 3px;
            font-size: 8px;
            color: #6b7280;
        }

        .report-header {
            text-align: right;
        }

        .report-header h2 {
            margin: 0;
            font-size: 14px;
            color: #1f2937;
            text-transform: uppercase;
        }

        .report-header p {
            margin: 2px 0 0;
            font-size: 8px;
            color: #4b5563;
        }

        /* Summary boxes */
        .summary-table {
            width: 100%;
            margin-bottom: 14px;
        }

        .summary-box {
            padding: 8px 10px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            text-align: center;
        }

        .summary-label {
            font-size: 7.5px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .summary-value {
            font-size: 12px;
            font-weight: bold;
            color: #111827;
        }

        /* Data table */
        .data-table {
            width: 100%;
            border: 1px solid #e5e7eb;
            margin-bottom: 14px;
        }

        .data-table th {
            background: #1f2937;
            color: #ffffff;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #374151;
        }

        .data-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 8px;
            vertical-align: top;
        }

        .data-table tr:nth-child(even) td {
            background: #f9fafb;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
        }

        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-received { background: #d1fae5; color: #065f46; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }

        .items-list {
            margin: 0;
            padding-left: 12px;
            list-style-type: square;
            font-size: 7.5px;
            color: #374151;
        }

        .footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            font-size: 7.5px;
            color: #9ca3af;
            text-align: right;
        }
    </style>
</head>
<body>

    {{-- KOP / HEADER --}}
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="brand-name">
                    TOKO GROSIR <span class="highlight">IJAD</span>
                </div>
                <div class="brand-description">
                    Sistem Manajemen Inventori & Penjualan Grosir
                </div>
            </td>
            <td class="report-header" style="vertical-align: middle;">
                <h2>Laporan Purchase Order</h2>
                <p>
                    Periode: {{ $dateFrom->translatedFormat('d F Y') }} — {{ $dateTo->translatedFormat('d F Y') }}
                </p>
                <p>
                    Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}
                </p>
            </td>
        </tr>
    </table>

    {{-- RINGKASAN METRIK --}}
    <table class="summary-table">
        <tr>
            <td style="width: 20%; padding: 0 3px;">
                <div class="summary-box">
                    <div class="summary-label">Total Purchase Order</div>
                    <div class="summary-value">{{ $totalOrders }}</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 3px;">
                <div class="summary-box">
                    <div class="summary-label">Menunggu (Pending)</div>
                    <div class="summary-value" style="color: #d97706;">{{ $totalPending }}</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 3px;">
                <div class="summary-box">
                    <div class="summary-label">Sudah Diterima</div>
                    <div class="summary-value" style="color: #059669;">{{ $totalReceived }}</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 3px;">
                <div class="summary-box">
                    <div class="summary-label">Total Kuantitas</div>
                    <div class="summary-value">{{ number_format($totalUnits, 0, ',', '.') }} Unit</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 3px;">
                <div class="summary-box">
                    <div class="summary-label">Estimasi Pengeluaran</div>
                    <div class="summary-value" style="color: #10b981;">Rp {{ number_format($totalEstimatedCost, 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- TABEL DATA --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 8%;">No. PO</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 18%;">Supplier</th>
                <th style="width: 10%;">Dibuat Oleh</th>
                <th style="width: 26%;">Detail Produk Dipesan</th>
                <th style="width: 8%;" class="text-center">Total Unit</th>
                <th style="width: 10%;" class="text-right">Estimasi Biaya</th>
                <th style="width: 6%;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchaseOrders as $index => $po)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold text-center">#{{ $po->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($po->order_date)->format('d/m/Y') }}</td>
                    <td>
                        <strong>{{ $po->supplier->name ?? '-' }}</strong><br>
                        <span style="color: #6b7280; font-size: 7px;">{{ $po->supplier->category->name ?? '-' }}</span>
                    </td>
                    <td>{{ $po->user->username ?? '-' }}</td>
                    <td>
                        <ul class="items-list">
                            @foreach($po->details as $d)
                                <li>
                                    {{ $d->product->name ?? $d->kode_produk }}
                                    (<strong>{{ $d->quantity }} {{ $d->product->base_unit ?? '' }}</strong>)
                                </li>
                            @endforeach
                        </ul>
                    </td>
                    <td class="text-center font-bold">{{ $po->total_units }}</td>
                    <td class="text-right font-bold">
                        Rp {{ number_format($po->estimated_cost, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @if($po->status === 'pending')
                            <span class="badge badge-pending">Menunggu</span>
                        @elseif($po->status === 'received')
                            <span class="badge badge-received">Diterima</span>
                        @elseif($po->status === 'cancelled')
                            <span class="badge badge-cancelled">Batal</span>
                        @else
                            <span class="badge">{{ $po->status }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px; color: #9ca3af;">
                        Tidak ada data purchase order pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($purchaseOrders->isNotEmpty())
            <tfoot>
                <tr style="background: #f3f4f6; font-weight: bold;">
                    <td colspan="6" class="text-right" style="padding: 6px 8px;">TOTAL</td>
                    <td class="text-center" style="padding: 6px 8px;">{{ number_format($totalUnits, 0, ',', '.') }}</td>
                    <td class="text-right" style="padding: 6px 8px;">Rp {{ number_format($totalEstimatedCost, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        Dokumen ini dibuat otomatis oleh Sistem Kasir & Inventori Toko Grosir IJAD — Halaman 1
    </div>

</body>
</html>
