<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>Laporan Penjualan Toko Grosir IJAD</title>

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
        }

        .page {
            width: 100%;
        }

        /* ======================================================= */
        /* HEADER                                                  */
        /* ======================================================= */

        .header-table {
            width: 100%;
            margin-bottom: 8px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .brand-name {
            margin: 0;
            font-size: 21px;
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
            font-size: 17px;
            color: #111827;
        }

        .report-header p {
            margin: 4px 0 0;
            font-size: 8px;
            color: #6b7280;
        }

        .yellow-line {
            width: 100%;
            height: 3px;
            background: #eab308;
            margin-bottom: 12px;
        }

        /* ======================================================= */
        /* INFORMASI LAPORAN                                       */
        /* ======================================================= */

        .information-box {
            width: 100%;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            margin-bottom: 12px;
        }

        .information-box td {
            width: 33.333%;
            padding: 9px 12px;
            vertical-align: top;
            border-right: 1px solid #dbe1e8;
        }

        .information-box td:last-child {
            border-right: none;
        }

        .information-label {
            display: block;
            font-size: 7px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 4px;
        }

        .information-value {
            display: block;
            font-size: 9px;
            font-weight: bold;
            color: #111827;
        }

        /* ======================================================= */
        /* RINGKASAN                                                */
        /* ======================================================= */

        .summary-wrapper {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: separate;
            border-spacing: 7px 0;
            margin-left: -7px;
        }

        .summary-wrapper > tbody > tr > td {
            width: 33.333%;
            vertical-align: top;
        }

        .summary-card {
            width: 100%;
            border: 1px solid #e5e7eb;
        }

        .summary-card td {
            padding: 10px 12px;
        }

        .summary-card.blue {
            background: #eff6ff;
            border-top: 4px solid #2563eb;
        }

        .summary-card.orange {
            background: #fff7ed;
            border-top: 4px solid #f97316;
        }

        .summary-card.green {
            background: #f0fdf4;
            border-top: 4px solid #16a34a;
        }

        .summary-label {
            font-size: 7px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .summary-value.blue {
            color: #1d4ed8;
        }

        .summary-value.orange {
            color: #ea580c;
        }

        .summary-value.green {
            color: #15803d;
        }

        .summary-note {
            font-size: 7px;
            color: #64748b;
        }

        /* ======================================================= */
        /* JUDUL TABEL                                             */
        /* ======================================================= */

        .section-title-table {
            width: 100%;
            margin-bottom: 7px;
        }

        .section-title-table td {
            vertical-align: bottom;
        }

        .section-title {
            margin: 0;
            font-size: 12px;
            font-weight: bold;
            color: #111827;
        }

        .section-description {
            margin: 2px 0 0;
            font-size: 7px;
            color: #64748b;
        }

        .transaction-count {
            text-align: right;
            color: #64748b;
            font-size: 8px;
        }

        /* ======================================================= */
        /* TABEL TRANSAKSI                                         */
        /* ======================================================= */

        .transaction-table {
            width: 100%;
            table-layout: fixed;
            border: 1px solid #d1d5db;
        }

        .transaction-table th {
            background: #1e293b;
            color: #ffffff;
            padding: 7px 5px;
            font-size: 7px;
            font-weight: bold;
            text-align: left;
            border: 1px solid #1e293b;
        }

        .transaction-table td {
            padding: 6px 5px;
            font-size: 7.5px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            background: #ffffff;
        }

        .transaction-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .transaction-table tfoot td {
            background: #f1f5f9;
            font-weight: bold;
            border-top: 2px solid #334155;
            padding: 7px 5px;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        .text-green {
            color: #15803d;
        }

        .text-orange {
            color: #ea580c;
        }

        .text-gray {
            color: #64748b;
        }

        .font-bold {
            font-weight: bold;
        }

        .transaction-code {
            font-weight: bold;
            color: #2563eb;
        }

        .product-item {
            line-height: 1.45;
            margin-bottom: 2px;
        }

        .product-item:last-child {
            margin-bottom: 0;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 6.5px;
            font-weight: bold;
            border-radius: 7px;
        }

        .badge-cash {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-transfer {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-refund {
            background: #ffedd5;
            color: #c2410c;
            margin-top: 2px;
        }

        .empty-row {
            text-align: center;
            padding: 25px !important;
            color: #94a3b8;
        }

        /* ======================================================= */
        /* FOOTER                                                  */
        /* ======================================================= */

        .footer {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 7px;
            color: #94a3b8;
        }

        .footer strong {
            color: #64748b;
        }
    </style>
</head>

<body>

<div class="page">

    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}
    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <h1 class="brand-name">
                    Toko Grosir
                    <span class="highlight">IJAD</span>
                </h1>

                <div class="brand-description">
                    Sistem Informasi Manajemen Toko
                </div>
            </td>

            <td style="width: 45%;" class="report-header">
                <h2>Laporan Penjualan</h2>

                <p>
                    Ringkasan dan detail transaksi penjualan
                </p>
            </td>
        </tr>
    </table>

    <div class="yellow-line"></div>

    {{-- ========================================================= --}}
    {{-- INFORMASI LAPORAN --}}
    {{-- ========================================================= --}}
    <table class="information-box">
        <tr>
            <td>
                <span class="information-label">
                    Periode Laporan
                </span>

                <span class="information-value">
                    {{ $dateFrom->locale('id')->isoFormat('D MMMM Y') }}
                    s.d.
                    {{ $dateTo->locale('id')->isoFormat('D MMMM Y') }}
                </span>
            </td>

            <td>
                <span class="information-label">
                    Tanggal Dicetak
                </span>

                <span class="information-value">
                    {{ now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB
                </span>
            </td>

            <td>
                <span class="information-label">
                    Dicetak Oleh
                </span>

                <span class="information-value">
                    {{ auth()->user()->username ?? 'Sistem' }}
                </span>
            </td>
        </tr>
    </table>

    {{-- ========================================================= --}}
    {{-- RINGKASAN --}}
    {{-- ========================================================= --}}
    <table class="summary-wrapper">
        <tr>

            {{-- Penjualan Kotor --}}
            <td>
                <table class="summary-card blue">
                    <tr>
                        <td>
                            <div class="summary-label">
                                Penjualan Kotor
                            </div>

                            <div class="summary-value blue">
                                Rp {{ number_format($totalSales, 0, ',', '.') }}
                            </div>

                            <div class="summary-note">
                                {{ $sales->count() }} transaksi
                            </div>
                        </td>
                    </tr>
                </table>
            </td>

            {{-- Total Refund --}}
            <td>
                <table class="summary-card orange">
                    <tr>
                        <td>
                            <div class="summary-label">
                                Total Refund
                            </div>

                            <div class="summary-value orange">
                                Rp {{ number_format($totalRefundNominal, 0, ',', '.') }}
                            </div>

                            <div class="summary-note">
                                {{ $totalRefunds }} transaksi,
                                {{ $totalRefundQty }} unit
                            </div>
                        </td>
                    </tr>
                </table>
            </td>

            {{-- Penjualan Bersih --}}
            <td>
                <table class="summary-card green">
                    <tr>
                        <td>
                            <div class="summary-label">
                                Penjualan Bersih
                            </div>

                            <div class="summary-value green">
                                Rp {{ number_format($netRevenue, 0, ',', '.') }}
                            </div>

                            <div class="summary-note">
                                Setelah dikurangi refund
                            </div>
                        </td>
                    </tr>
                </table>
            </td>

        </tr>
    </table>

    {{-- ========================================================= --}}
    {{-- JUDUL DETAIL TRANSAKSI --}}
    {{-- ========================================================= --}}
    <table class="section-title-table">
        <tr>
            <td>
                <h3 class="section-title">
                    Detail Transaksi
                </h3>

                <p class="section-description">
                    Daftar transaksi sesuai periode laporan.
                </p>
            </td>

            <td class="transaction-count">
                Total {{ $sales->count() }} transaksi
            </td>
        </tr>
    </table>

    {{-- ========================================================= --}}
    {{-- TABEL TRANSAKSI --}}
    {{-- ========================================================= --}}
    <table class="transaction-table">

        <thead>
            <tr>
                <th class="text-center" style="width: 4%;">
                    No
                </th>

                <th style="width: 10%;">
                    Transaksi
                </th>

                <th style="width: 10%;">
                    Tanggal
                </th>

                <th style="width: 24%;">
                    Produk
                </th>

                <th class="text-center" style="width: 9%;">
                    Metode
                </th>

                <th class="text-right" style="width: 12%;">
                    Kotor
                </th>

                <th class="text-right" style="width: 11%;">
                    Refund
                </th>

                <th class="text-right" style="width: 12%;">
                    Bersih
                </th>

                <th style="width: 8%;">
                    Kasir
                </th>
            </tr>
        </thead>

        <tbody>

            @forelse($sales as $index => $sale)

                @php
                    $saleRefundNominal = 0;

                    foreach ($sale->refunds as $refund) {
                        $saleDetail = $sale->details
                            ->where('kode_produk', $refund->kode_produk)
                            ->first();

                        if ($saleDetail) {
                            $saleRefundNominal +=
                                $refund->quantity * $saleDetail->unit_price;
                        }
                    }

                    $saleNetRevenue =
                        $sale->total_price - $saleRefundNominal;
                @endphp

                <tr>

                    {{-- Nomor --}}
                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>

                    {{-- Transaksi --}}
                    <td>
                        <span class="transaction-code">
                            #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}
                        </span>
                    </td>

                    {{-- Tanggal --}}
                    <td>
                        <span class="font-bold">
                            {{ \Carbon\Carbon::parse($sale->date)->format('d/m/Y') }}
                        </span>

                        <br>

                        <span class="text-gray">
                            {{ $sale->created_at->format('H:i') }} WIB
                        </span>
                    </td>

                    {{-- Produk --}}
                    <td>
                        @foreach($sale->details as $detail)
                            <div class="product-item">
                                {{ $detail->product->name ?? $detail->kode_produk }}

                                <span class="text-gray">
                                    ({{ $detail->quantity }}
                                    {{ $detail->product->base_unit ?? 'unit' }})
                                </span>
                            </div>
                        @endforeach
                    </td>

                    {{-- Metode --}}
                    <td class="text-center">
                        @if($sale->payment_method === 'cash')
                            <span class="badge badge-cash">
                                Tunai
                            </span>
                        @else
                            <span class="badge badge-transfer">
                                Transfer
                            </span>
                        @endif
                    </td>

                    {{-- Kotor --}}
                    <td class="text-right font-bold">
                        Rp {{ number_format($sale->total_price, 0, ',', '.') }}
                    </td>

                    {{-- Refund --}}
                    <td class="text-right">
                        @if($saleRefundNominal > 0)
                            <span class="text-orange font-bold">
                                Rp {{ number_format($saleRefundNominal, 0, ',', '.') }}
                            </span>

                            <br>

                            <span class="badge badge-refund">
                                {{ $sale->refunds->sum('quantity') }} unit
                            </span>
                        @else
                            <span class="text-gray">
                                -
                            </span>
                        @endif
                    </td>

                    {{-- Bersih --}}
                    <td class="text-right font-bold text-green">
                        Rp {{ number_format($saleNetRevenue, 0, ',', '.') }}
                    </td>

                    {{-- Kasir --}}
                    <td>
                        {{ $sale->user->username ?? '-' }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="9" class="empty-row">
                        Tidak ada transaksi pada periode yang dipilih.
                    </td>
                </tr>

            @endforelse

        </tbody>

        @if($sales->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right">
                        Total Keseluruhan
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($totalSales, 0, ',', '.') }}
                    </td>

                    <td class="text-right text-orange">
                        Rp {{ number_format($totalRefundNominal, 0, ',', '.') }}
                    </td>

                    <td class="text-right text-green">
                        Rp {{ number_format($netRevenue, 0, ',', '.') }}
                    </td>

                    <td></td>
                </tr>
            </tfoot>
        @endif

    </table>

    {{-- ========================================================= --}}
    {{-- FOOTER --}}
    {{-- ========================================================= --}}
    <div class="footer">
        <strong>Toko Grosir IJAD</strong>
        — Laporan dibuat otomatis oleh sistem pada
        {{ now()->format('d/m/Y H:i') }} WIB.
    </div>

</div>

</body>
</html>