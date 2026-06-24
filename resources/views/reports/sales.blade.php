@extends('layouts.app')

@section('title', 'Laporan Penjualan')
@section('page-title', 'Laporan Penjualan')
@section('page-subtitle', 'Ringkasan dan detail penjualan Toko Grosir IJAD')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Parameter ekspor
    |--------------------------------------------------------------------------
    |
    | PDF dan Excel harus memakai periode yang sama dengan laporan
    | yang sedang ditampilkan.
    |
    */
    $exportQuery = [
        'filter' => $filter,
    ];

    if ($filter === 'custom') {
        $exportQuery['date_from'] = $dateFrom->format('Y-m-d');
        $exportQuery['date_to'] = $dateTo->format('Y-m-d');
    }

    $refundPercentage = $totalSales > 0
        ? ($totalRefundNominal / $totalSales) * 100
        : 0;
@endphp

<div class="space-y-6">

    {{-- ========================================================= --}}
    {{-- PESAN VALIDASI --}}
    {{-- ========================================================= --}}
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-100">
                    ⚠️
                </div>

                <div>
                    <p class="text-sm font-semibold">
                        Periode laporan belum benar
                    </p>

                    <ul class="mt-1 list-inside list-disc space-y-1 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- PESAN BERHASIL --}}
    {{-- ========================================================= --}}
    @if(session('success'))
        <div class="flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-700">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-green-100">
                ✅
            </div>

            <div>
                <p class="text-sm font-semibold">
                    Berhasil
                </p>

                <p class="mt-0.5 text-sm">
                    {{ session('success') }}
                </p>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- FILTER PERIODE --}}
    {{-- ========================================================= --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <form
            method="GET"
            action="{{ route('reports.sales') }}"
            x-data="{
                filter: @js(old('filter', request('filter', $filter)))
            }"
        >
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">

                {{-- Filter cepat --}}
                <div>
                    <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-400">
                        Pilih Periode
                    </p>

                    <div class="flex flex-wrap gap-2">
                        <button
                            type="submit"
                            name="filter"
                            value="today"
                            @click="filter = 'today'"
                            :class="filter === 'today'
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="rounded-xl px-5 py-2.5 text-sm font-semibold transition"
                        >
                            Hari Ini
                        </button>

                        <button
                            type="submit"
                            name="filter"
                            value="this_month"
                            @click="filter = 'this_month'"
                            :class="filter === 'this_month'
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="rounded-xl px-5 py-2.5 text-sm font-semibold transition"
                        >
                            Bulan Ini
                        </button>

                        <button
                            type="submit"
                            name="filter"
                            value="this_year"
                            @click="filter = 'this_year'"
                            :class="filter === 'this_year'
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="rounded-xl px-5 py-2.5 text-sm font-semibold transition"
                        >
                            Tahun Ini
                        </button>

                        <button
                            type="button"
                            @click="filter = 'custom'"
                            :class="filter === 'custom'
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="rounded-xl px-5 py-2.5 text-sm font-semibold transition"
                        >
                            Rentang Manual
                        </button>
                    </div>
                </div>

                {{-- Tanggal manual --}}
                <div
                    x-show="filter === 'custom'"
                    x-cloak
                    class="flex flex-col gap-3 sm:flex-row sm:items-end"
                >
                    <div>
                        <label
                            for="date-from"
                            class="mb-1.5 block text-xs font-medium text-gray-500"
                        >
                            Dari Tanggal
                        </label>

                        <input
                            id="date-from"
                            type="date"
                            name="date_from"
                            value="{{ old(
                                'date_from',
                                request(
                                    'date_from',
                                    $dateFrom->format('Y-m-d')
                                )
                            ) }}"
                            x-bind:disabled="filter !== 'custom'"
                            x-bind:required="filter === 'custom'"
                            class="h-11 rounded-xl border border-gray-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>

                    <div>
                        <label
                            for="date-to"
                            class="mb-1.5 block text-xs font-medium text-gray-500"
                        >
                            Sampai Tanggal
                        </label>

                        <input
                            id="date-to"
                            type="date"
                            name="date_to"
                            value="{{ old(
                                'date_to',
                                request(
                                    'date_to',
                                    $dateTo->format('Y-m-d')
                                )
                            ) }}"
                            x-bind:disabled="filter !== 'custom'"
                            x-bind:required="filter === 'custom'"
                            class="h-11 rounded-xl border border-gray-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>

                    <button
                        type="submit"
                        name="filter"
                        value="custom"
                        class="h-11 rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-700"
                    >
                        Terapkan
                    </button>
                </div>
            </div>

            {{-- Informasi periode --}}
            <div class="mt-4 border-t border-gray-100 pt-4">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div class="flex items-start gap-2 text-sm sm:items-center">
                        <span class="text-gray-400">
                            📅
                        </span>

                        <div>
                            <span class="text-gray-500">
                                Periode laporan:
                            </span>

                            <strong class="ml-1 text-gray-800">
                                {{ $dateFrom->locale('id')->isoFormat('D MMMM Y') }}
                                —
                                {{ $dateTo->locale('id')->isoFormat('D MMMM Y') }}
                            </strong>
                        </div>
                    </div>

                    <p class="text-xs text-gray-400">
                        Penjualan bersih = penjualan kotor − nominal refund
                    </p>
                </div>
            </div>
        </form>
    </div>

    {{-- ========================================================= --}}
    {{-- RINGKASAN LAPORAN --}}
    {{-- ========================================================= --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

        {{-- Penjualan bersih --}}
        <div class="rounded-2xl bg-gray-900 p-6 text-white shadow-sm sm:col-span-2 xl:col-span-2">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5">
                        <span class="text-sm">
                            📈
                        </span>

                        <span class="text-xs font-semibold text-gray-200">
                            Pendapatan Bersih
                        </span>
                    </div>

                    <p class="mt-5 text-sm text-gray-300">
                        Penjualan Bersih
                    </p>

                    <p class="mt-1 text-3xl font-bold lg:text-4xl">
                        Rp {{ number_format($netRevenue, 0, ',', '.') }}
                    </p>

                    <p class="mt-3 text-xs text-gray-400">
                        Pendapatan setelah dikurangi seluruh nominal refund
                    </p>
                </div>

                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-green-500/20 text-3xl">
                    💹
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3 border-t border-white/10 pt-5">
                <div>
                    <p class="text-xs text-gray-400">
                        Jumlah Transaksi
                    </p>

                    <p class="mt-1 text-lg font-bold">
                        {{ $sales->count() }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-400">
                        Unit Direfund
                    </p>

                    <p class="mt-1 text-lg font-bold">
                        {{ number_format($totalRefundQty, 0, ',', '.') }} unit
                    </p>
                </div>
            </div>
        </div>

        {{-- Penjualan kotor --}}
        <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Penjualan Kotor
                    </p>

                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        Rp {{ number_format($totalSales, 0, ',', '.') }}
                    </p>
                </div>

                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-xl">
                    💰
                </div>
            </div>

            <div class="mt-5 border-t border-gray-100 pt-4">
                <p class="text-xs text-gray-500">
                    {{ $sales->count() }} transaksi pada periode ini
                </p>
            </div>
        </div>

        {{-- Total refund --}}
        <div class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Nominal Refund
                    </p>

                    <p class="mt-2 text-2xl font-bold text-orange-600">
                        Rp {{ number_format(
                            $totalRefundNominal,
                            0,
                            ',',
                            '.'
                        ) }}
                    </p>
                </div>

                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-xl">
                    ↩️
                </div>
            </div>

            <div class="mt-5 border-t border-gray-100 pt-4">
                <p class="text-xs text-gray-500">
                    {{ $totalRefunds }} transaksi refund ·
                    {{ $totalRefundQty }} unit
                </p>
            </div>
        </div>

        {{-- Persentase refund --}}
        <div class="rounded-2xl border border-purple-100 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Persentase Refund
                    </p>

                    <p class="mt-2 text-2xl font-bold text-purple-600">
                        {{ number_format(
                            $refundPercentage,
                            1,
                            ',',
                            '.'
                        ) }}%
                    </p>
                </div>

                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-xl">
                    📊
                </div>
            </div>

            <div class="mt-5 border-t border-gray-100 pt-4">
                <p class="text-xs text-gray-500">
                    Dari total penjualan kotor
                </p>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- DETAIL TRANSAKSI --}}
    {{-- ========================================================= --}}
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">

        {{-- Header tabel --}}
        <div class="flex flex-col justify-between gap-4 border-b border-gray-100 p-5 sm:flex-row sm:items-center">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">
                    Detail Transaksi
                </h3>

                <p class="mt-0.5 text-sm text-gray-500">
                    {{ $sales->count() }} transaksi ditemukan
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route(
                        'reports.sales.pdf',
                        $exportQuery
                    ) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700"
                >
                    📄 Buka PDF
                </a>

                <a
                    href="{{ route(
                        'reports.sales.excel',
                        $exportQuery
                    ) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700"
                >
                    📊 Export Excel
                </a>
            </div>
        </div>

        {{-- Tabel transaksi --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-5 py-3.5 text-left font-medium">
                            #
                        </th>

                        <th class="px-5 py-3.5 text-left font-medium">
                            Tanggal
                        </th>

                        <th class="px-5 py-3.5 text-left font-medium">
                            Produk
                        </th>

                        <th class="px-5 py-3.5 text-left font-medium">
                            Metode
                        </th>

                        <th class="px-5 py-3.5 text-right font-medium">
                            Kotor
                        </th>

                        <th class="px-5 py-3.5 text-right font-medium">
                            Refund
                        </th>

                        <th class="px-5 py-3.5 text-right font-medium">
                            Bersih
                        </th>

                        <th class="px-5 py-3.5 text-left font-medium">
                            Kasir
                        </th>

                        <th class="px-5 py-3.5 text-center font-medium">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($sales as $index => $sale)

                        @php
                            /*
                            |--------------------------------------------------------------------------
                            | Hasil per transaksi
                            |--------------------------------------------------------------------------
                            |
                            | Nilai ini sudah dihitung di ReportController.
                            | Blade hanya menampilkannya.
                            |
                            */
                            $saleRefundNominal = (float) $sale->refund_nominal;
                            $saleRefundQuantity = (int) $sale->refund_quantity;
                            $saleNetRevenue = (float) $sale->net_revenue;
                        @endphp

                        <tr class="transition hover:bg-gray-50">

                            {{-- Nomor --}}
                            <td class="px-5 py-4 text-gray-500">
                                {{ $index + 1 }}
                            </td>

                            {{-- Tanggal --}}
                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="font-medium text-gray-800">
                                    {{ $sale->date
                                        ->locale('id')
                                        ->isoFormat('D MMM Y') }}
                                </p>

                                <p class="mt-0.5 text-xs text-gray-400">
                                    {{ $sale->created_at->format('H:i') }} WIB
                                </p>

                                <p class="mt-1 font-mono text-[11px] text-gray-400">
                                    #{{ str_pad(
                                        (string) $sale->id,
                                        6,
                                        '0',
                                        STR_PAD_LEFT
                                    ) }}
                                </p>
                            </td>

                            {{-- Produk --}}
                            <td class="min-w-64 px-5 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($sale->details as $detail)
                                        <span class="inline-flex rounded-lg bg-gray-100 px-2.5 py-1 text-xs text-gray-600">
                                            {{ $detail->product->name
                                                ?? $detail->kode_produk }}

                                            ({{ $detail->quantity }}
                                            {{ $detail->product->base_unit
                                                ?? 'unit' }})
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            {{-- Metode pembayaran --}}
                            <td class="px-5 py-4">
                                @if($sale->payment_method === 'cash')
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">
                                        {{ $sale->payment_method_label }}
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700">
                                        {{ $sale->payment_method_label }}
                                    </span>
                                @endif
                            </td>

                            {{-- Penjualan kotor --}}
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <span class="font-semibold text-gray-800">
                                    Rp {{ number_format(
                                        $sale->total_price,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </span>
                            </td>

                            {{-- Refund --}}
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                @if($saleRefundNominal > 0)
                                    <span class="font-semibold text-orange-600">
                                        Rp {{ number_format(
                                            $saleRefundNominal,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </span>

                                    <p class="mt-0.5 text-xs text-orange-400">
                                        {{ $saleRefundQuantity }} unit
                                    </p>
                                @else
                                    <span class="text-gray-300">
                                        —
                                    </span>
                                @endif
                            </td>

                            {{-- Penjualan bersih --}}
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <span class="font-bold text-green-700">
                                    Rp {{ number_format(
                                        $saleNetRevenue,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </span>
                            </td>

                            {{-- Kasir --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-yellow-400 text-xs font-bold text-gray-900">
                                        {{ strtoupper(
                                            substr(
                                                $sale->user->username
                                                    ?? 'U',
                                                0,
                                                1
                                            )
                                        ) }}
                                    </div>

                                    <span class="text-gray-600">
                                        {{ $sale->user->username ?? '-' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-4 text-center">
                                <a
                                    href="{{ route(
                                        'sales.show',
                                        $sale
                                    ) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 transition hover:bg-blue-100"
                                >
                                    👁️ Detail
                                </a>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td
                                colspan="9"
                                class="py-14 text-center text-gray-400"
                            >
                                <div class="mb-3 text-5xl">
                                    📊
                                </div>

                                <p class="font-medium">
                                    Tidak ada transaksi
                                </p>

                                <p class="mt-1 text-xs">
                                    Tidak ditemukan transaksi pada periode yang dipilih.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if($sales->isNotEmpty())
                    <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                        <tr>
                            <td
                                colspan="4"
                                class="px-5 py-4 text-right text-sm font-bold text-gray-700"
                            >
                                TOTAL
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-right font-bold text-gray-900">
                                Rp {{ number_format(
                                    $totalSales,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-right font-bold text-orange-600">
                                Rp {{ number_format(
                                    $totalRefundNominal,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                                <p class="mt-0.5 text-xs font-normal text-orange-400">
                                    {{ $totalRefundQty }} unit
                                </p>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-right font-bold text-green-700">
                                Rp {{ number_format(
                                    $netRevenue,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </td>

                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        {{-- Keterangan --}}
        <div class="border-t border-gray-100 bg-gray-50 px-5 py-4">
            <div class="flex flex-col justify-between gap-3 text-xs text-gray-500 sm:flex-row sm:items-center">
                <p>
                    <strong class="text-gray-700">
                        {{ $totalRefunds }}
                    </strong>
                    transaksi mengalami refund dengan total
                    <strong class="text-orange-600">
                        {{ $totalRefundQty }} unit
                    </strong>.
                </p>

                <p>
                    Dicetak berdasarkan periode
                    {{ $dateFrom->format('d/m/Y') }}
                    sampai
                    {{ $dateTo->format('d/m/Y') }}.
                </p>
            </div>
        </div>
    </div>
</div>

@endsection