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
            margin-bottom: 12px;
            background: #eab308;
        }

        /* ======================================================= */
        /* INFORMASI LAPORAN                                       */
        /* ======================================================= */

        .information-box {
            width: 100%;
            margin-bottom: 12px;
            border: 1px solid #e5e7eb;
            background: #f8fafc;
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
            margin-bottom: 4px;
            font-size: 7px;
            text-transform: uppercase;
            color: #64748b;
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
            margin-left: -7px;
            border-collapse: separate;
            border-spacing: 7px 0;
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
            border-top: 4px solid #2563eb;
            background: #eff6ff;
        }

        .summary-card.orange {
            border-top: 4px solid #f97316;
            background: #fff7ed;
        }

        .summary-card.green {
            border-top: 4px solid #16a34a;
            background: #f0fdf4;
        }

        .summary-label {
            margin-bottom: 5px;
            font-size: 7px;
            text-transform: uppercase;
            color: #64748b;
        }

        .summary-value {
            margin-bottom: 3px;
            font-size: 16px;
            font-weight: bold;
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
        /* INFORMASI RUMUS                                         */
        /* ======================================================= */

        .formula-box {
            width: 100%;
            margin-bottom: 12px;
            border: 1px solid #dbeafe;
            background: #eff6ff;
        }

        .formula-box td {
            padding: 7px 10px;
        }

        .formula-label {
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1d4ed8;
        }

        .formula-value {
            text-align: right;
            font-size: 8px;
            font-weight: bold;
            color: #1e3a8a;
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
            font-size: 8px;
            color: #64748b;
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
            padding: 7px 5px;
            border: 1px solid #1e293b;
            background: #1e293b;
            color: #ffffff;
            font-size: 7px;
            font-weight: bold;
            text-align: left;
        }

        .transaction-table td {
            padding: 6px 5px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            background: #ffffff;
            font-size: 7.5px;
        }

        .transaction-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .transaction-table tfoot td {
            padding: 7px 5px;
            border-top: 2px solid #334155;
            background: #f1f5f9;
            font-weight: bold;
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
            margin-bottom: 3px;
            line-height: 1.45;
        }

        .product-item:last-child {
            margin-bottom: 0;
        }

        .product-description {
            font-size: 6.5px;
            color: #94a3b8;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 7px;
            font-size: 6.5px;
            font-weight: bold;
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
            margin-top: 2px;
            background: #ffedd5;
            color: #c2410c;
        }

        .empty-row {
            padding: 25px !important;
            text-align: center;
            color: #94a3b8;
        }

        /* ======================================================= */
        /* KETERANGAN                                              */
        /* ======================================================= */

        .report-note {
            width: 100%;
            margin-top: 10px;
            border: 1px solid #e5e7eb;
            background: #f8fafc;
        }

        .report-note td {
            padding: 7px 10px;
            font-size: 7px;
            color: #64748b;
        }

        .report-note strong {
            color: #334155;
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
                    {{ now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }}
                    WIB
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
                                Rp {{ number_format(
                                    $totalSales,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </div>

                            <div class="summary-note">
                                {{ $sales->count() }} transaksi penjualan
                            </div>
                        </td>
                    </tr>
                </table>
            </td>

            {{-- Nominal Refund --}}
            <td>
                <table class="summary-card orange">
                    <tr>
                        <td>
                            <div class="summary-label">
                                Nominal Refund
                            </div>

                            <div class="summary-value orange">
                                Rp {{ number_format(
                                    $totalRefundNominal,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </div>

                            <div class="summary-note">
                                {{ $totalRefunds }} transaksi refund,
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
                                Rp {{ number_format(
                                    $netRevenue,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </div>

                            <div class="summary-note">
                                Setelah dikurangi nominal refund
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ========================================================= --}}
    {{-- RUMUS PENJUALAN BERSIH --}}
    {{-- ========================================================= --}}
    <table class="formula-box">
        <tr>
            <td style="width: 35%;">
                <span class="formula-label">
                    Rumus Penjualan Bersih
                </span>
            </td>

            <td class="formula-value" style="width: 65%;">
                Penjualan Kotor − Nominal Refund =
                Rp {{ number_format($totalSales, 0, ',', '.') }}
                −
                Rp {{ number_format($totalRefundNominal, 0, ',', '.') }}
                =
                Rp {{ number_format($netRevenue, 0, ',', '.') }}
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
                    /*
                    |--------------------------------------------------------------------------
                    | Nilai transaksi
                    |--------------------------------------------------------------------------
                    |
                    | Seluruh nilai sudah dihitung oleh ReportController.
                    | PDF tidak menghitung refund ulang.
                    |
                    */
                    $saleRefundNominal = (float) $sale->refund_nominal;
                    $saleRefundQuantity = (int) $sale->refund_quantity;
                    $saleNetRevenue = (float) $sale->net_revenue;
                @endphp

                <tr>

                    {{-- Nomor --}}
                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>

                    {{-- Nomor transaksi --}}
                    <td>
                        <span class="transaction-code">
                            #{{ str_pad(
                                (string) $sale->id,
                                6,
                                '0',
                                STR_PAD_LEFT
                            ) }}
                        </span>
                    </td>

                    {{-- Tanggal --}}
                    <td>
                        <span class="font-bold">
                            {{ $sale->date->format('d/m/Y') }}
                        </span>

                        <br>

                        <span class="text-gray">
                            {{ $sale->created_at->format('H:i') }}
                            WIB
                        </span>
                    </td>

                    {{-- Produk --}}
                    <td>
                        @foreach($sale->details as $detail)
                            <div class="product-item">
                                <span class="font-bold">
                                    {{ $detail->product->name
                                        ?? $detail->kode_produk }}
                                </span>

                                <span class="text-gray">
                                    ({{ $detail->quantity }}
                                    {{ $detail->product->base_unit
                                        ?? 'unit' }})
                                </span>

                                @if(!empty($detail->description))
                                    <br>

                                    <span class="product-description">
                                        {{ $detail->description }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </td>

                    {{-- Metode pembayaran --}}
                    <td class="text-center">
                        @if($sale->payment_method === 'cash')
                            <span class="badge badge-cash">
                                {{ $sale->payment_method_label }}
                            </span>
                        @else
                            <span class="badge badge-transfer">
                                {{ $sale->payment_method_label }}
                            </span>
                        @endif
                    </td>

                    {{-- Penjualan kotor --}}
                    <td class="text-right font-bold">
                        Rp {{ number_format(
                            $sale->total_price,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    {{-- Refund --}}
                    <td class="text-right">
                        @if($saleRefundNominal > 0)
                            <span class="text-orange font-bold">
                                Rp {{ number_format(
                                    $saleRefundNominal,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </span>

                            <br>

                            <span class="badge badge-refund">
                                {{ $saleRefundQuantity }} unit
                            </span>
                        @else
                            <span class="text-gray">
                                -
                            </span>
                        @endif
                    </td>

                    {{-- Penjualan bersih --}}
                    <td class="text-right font-bold text-green">
                        Rp {{ number_format(
                            $saleNetRevenue,
                            0,
                            ',',
                            '.'
                        ) }}
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

        @if($sales->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right">
                        Total Keseluruhan
                    </td>

                    <td class="text-right">
                        Rp {{ number_format(
                            $totalSales,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td class="text-right text-orange">
                        Rp {{ number_format(
                            $totalRefundNominal,
                            0,
                            ',',
                            '.'
                        ) }}

                        <br>

                        <span style="font-size: 6.5px;">
                            {{ $totalRefundQty }} unit
                        </span>
                    </td>

                    <td class="text-right text-green">
                        Rp {{ number_format(
                            $netRevenue,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td></td>
                </tr>
            </tfoot>
        @endif

    </table>

    {{-- ========================================================= --}}
    {{-- KETERANGAN --}}
    {{-- ========================================================= --}}
    <table class="report-note">
        <tr>
            <td>
                <strong>{{ $totalRefunds }}</strong>
                transaksi penjualan mengalami refund dengan total
                <strong>{{ $totalRefundQty }} unit</strong>
                dan nominal
                <strong>
                    Rp {{ number_format(
                        $totalRefundNominal,
                        0,
                        ',',
                        '.'
                    ) }}
                </strong>.
            </td>
        </tr>
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