@extends('layouts.app')

@section('title', 'Laporan Penjualan')
@section('page-title', 'Laporan Penjualan')
@section('page-subtitle', 'Ringkasan dan detail penjualan')

@section('content')

{{-- ================================================================ --}}
{{-- FILTER PERIODE --}}
{{-- ================================================================ --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
    <form
        method="GET"
        action="{{ route('reports.sales') }}"
        x-data="{ filter: @js($filter) }"
    >
        <div class="flex flex-col xl:flex-row xl:items-end gap-4">

            {{-- Filter cepat --}}
            <div class="flex flex-wrap gap-2">
                <button
                    type="submit"
                    name="filter"
                    value="today"
                    @click="filter = 'today'"
                    :class="filter === 'today'
                        ? 'bg-blue-600 text-white shadow-sm'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold transition"
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
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold transition"
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
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold transition"
                >
                    Tahun Ini
                </button>

                <button
                    type="button"
                    @click="filter = 'custom'"
                    :class="filter === 'custom'
                        ? 'bg-blue-600 text-white shadow-sm'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold transition"
                >
                    Rentang Manual
                </button>
            </div>

            {{-- Tanggal manual --}}
            <div
                x-show="filter === 'custom'"
                x-cloak
                class="flex flex-col sm:flex-row sm:items-end gap-3"
            >
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">
                        Dari Tanggal
                    </label>

                    <input
                        type="date"
                        name="date_from"
                        value="{{ request('date_from', $dateFrom?->format('Y-m-d')) }}"
                        class="h-11 px-4 border border-gray-300 rounded-xl text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">
                        Sampai Tanggal
                    </label>

                    <input
                        type="date"
                        name="date_to"
                        value="{{ request('date_to', $dateTo?->format('Y-m-d')) }}"
                        class="h-11 px-4 border border-gray-300 rounded-xl text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <button
                    type="submit"
                    name="filter"
                    value="custom"
                    class="h-11 bg-blue-600 hover:bg-blue-700 text-white
                           px-5 rounded-xl text-sm font-semibold transition"
                >
                    Terapkan
                </button>
            </div>
        </div>

        {{-- Informasi periode --}}
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center gap-2 text-sm">
                <span class="text-gray-400">📅</span>

                <span class="text-gray-500">Periode laporan:</span>

                <strong class="text-gray-800">
                    {{ $dateFrom->locale('id')->isoFormat('D MMMM Y') }}
                    —
                    {{ $dateTo->locale('id')->isoFormat('D MMMM Y') }}
                </strong>
            </div>
        </div>
    </form>
</div>

{{-- ================================================================ --}}
{{-- RINGKASAN LAPORAN --}}
{{-- ================================================================ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">

    {{-- Penjualan bersih sebagai kartu utama --}}
    <div class="sm:col-span-2 xl:col-span-2 bg-gray-900 rounded-2xl p-6 text-white shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-full">
                    <span class="text-sm">📈</span>
                    <span class="text-xs font-semibold text-gray-200">
                        Pendapatan Bersih
                    </span>
                </div>

                <p class="text-sm text-gray-300 mt-5">
                    Penjualan Bersih
                </p>

                <p class="text-3xl lg:text-4xl font-bold mt-1">
                    Rp {{ number_format($netRevenue, 0, ',', '.') }}
                </p>

                <p class="text-xs text-gray-400 mt-3">
                    Pendapatan setelah dikurangi seluruh refund
                </p>
            </div>

            <div class="w-14 h-14 rounded-2xl bg-green-500/20
                        flex items-center justify-center text-3xl">
                💹
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 mt-6 pt-5 border-t border-white/10">
            <div>
                <p class="text-xs text-gray-400">Jumlah Transaksi</p>
                <p class="font-bold text-lg mt-1">
                    {{ $sales->count() }}
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-400">Produk Direfund</p>
                <p class="font-bold text-lg mt-1">
                    {{ $totalRefundQty }} unit
                </p>
            </div>
        </div>
    </div>

    {{-- Penjualan kotor --}}
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-gray-500">
                    Penjualan Kotor
                </p>

                <p class="text-2xl font-bold text-gray-900 mt-2">
                    Rp {{ number_format($totalSales, 0, ',', '.') }}
                </p>
            </div>

            <div class="w-11 h-11 rounded-xl bg-blue-100
                        flex items-center justify-center text-xl">
                💰
            </div>
        </div>

        <div class="mt-5 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-500">
                {{ $sales->count() }} transaksi pada periode ini
            </p>
        </div>
    </div>

    {{-- Total refund --}}
    <div class="bg-white rounded-2xl border border-orange-100 shadow-sm p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-gray-500">
                    Total Refund
                </p>

                <p class="text-2xl font-bold text-orange-600 mt-2">
                    Rp {{ number_format($totalRefundNominal, 0, ',', '.') }}
                </p>
            </div>

            <div class="w-11 h-11 rounded-xl bg-orange-100
                        flex items-center justify-center text-xl">
                ↩️
            </div>
        </div>

        <div class="mt-5 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-500">
                {{ $totalRefunds }} transaksi · {{ $totalRefundQty }} unit
            </p>
        </div>
    </div>

    {{-- Persentase refund --}}
    <div class="bg-white rounded-2xl border border-purple-100 shadow-sm p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-gray-500">
                    Persentase Refund
                </p>

                <p class="text-2xl font-bold text-purple-600 mt-2">
                    {{ $totalSales > 0
                        ? number_format(($totalRefundNominal / $totalSales) * 100, 1)
                        : 0 }}%
                </p>
            </div>

            <div class="w-11 h-11 rounded-xl bg-purple-100
                        flex items-center justify-center text-xl">
                📊
            </div>
        </div>

        <div class="mt-5 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-500">
                Dari total nilai penjualan
            </p>
        </div>
    </div>
</div>

{{-- ================================================================ --}}
{{-- DETAIL TRANSAKSI --}}
{{-- ================================================================ --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

    {{-- Header tabel --}}
    <div class="flex flex-col sm:flex-row sm:items-center
                justify-between gap-4 p-5 border-b border-gray-100">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">
                Detail Transaksi
            </h3>

            <p class="text-gray-500 text-sm mt-0.5">
                {{ $sales->count() }} transaksi ditemukan
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('reports.sales.pdf', request()->query()) }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center justify-center gap-2
                        bg-red-600 hover:bg-red-700 text-white
                        px-4 py-2.5 rounded-xl text-sm font-semibold transition">
                    📄 Print PDF
            </a>

            <a
                href="{{ route('reports.sales.excel', request()->query()) }}"
                class="inline-flex items-center justify-center gap-2
                       bg-green-600 hover:bg-green-700 text-white
                       px-4 py-2.5 rounded-xl text-sm font-semibold transition"
            >
                📊 Export Excel
            </a>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="text-left px-5 py-3.5 font-medium">#</th>
                    <th class="text-left px-5 py-3.5 font-medium">Tanggal</th>
                    <th class="text-left px-5 py-3.5 font-medium">Produk</th>
                    <th class="text-left px-5 py-3.5 font-medium">Metode</th>
                    <th class="text-right px-5 py-3.5 font-medium">Kotor</th>
                    <th class="text-right px-5 py-3.5 font-medium">Refund</th>
                    <th class="text-right px-5 py-3.5 font-medium">Bersih</th>
                    <th class="text-left px-5 py-3.5 font-medium">Kasir</th>
                    <th class="text-center px-5 py-3.5 font-medium">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse($sales as $index => $sale)

                    @php
                        $refundNominal = 0;

                        foreach ($sale->refunds as $refund) {
                            $detail = $sale->details
                                ->where('kode_produk', $refund->kode_produk)
                                ->first();

                            if ($detail) {
                                $refundNominal +=
                                    $refund->quantity * $detail->unit_price;
                            }
                        }

                        $saleNet = $sale->total_price - $refundNominal;
                    @endphp

                    <tr class="hover:bg-gray-50 transition">

                        {{-- Nomor --}}
                        <td class="px-5 py-4 text-gray-500">
                            {{ $index + 1 }}
                        </td>

                        {{-- Tanggal --}}
                        <td class="px-5 py-4 whitespace-nowrap">
                            <p class="font-medium text-gray-800">
                                {{ \Carbon\Carbon::parse($sale->date)
                                    ->locale('id')
                                    ->isoFormat('D MMM Y') }}
                            </p>

                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $sale->created_at->format('H:i') }} WIB
                            </p>
                        </td>

                        {{-- Produk --}}
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($sale->details as $detail)
                                    <span class="inline-flex bg-gray-100 text-gray-600
                                                 text-xs px-2.5 py-1 rounded-lg">
                                        {{ $detail->product->name ?? $detail->kode_produk }}
                                        ({{ $detail->quantity }})
                                    </span>
                                @endforeach
                            </div>
                        </td>

                        {{-- Metode --}}
                        <td class="px-5 py-4">
                            <span class="inline-flex bg-blue-100 text-blue-700
                                         text-xs px-2.5 py-1 rounded-full font-medium">
                                {{ $sale->payment_method_label }}
                            </span>
                        </td>

                        {{-- Total kotor --}}
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <span class="font-semibold text-gray-800">
                                Rp {{ number_format($sale->total_price, 0, ',', '.') }}
                            </span>
                        </td>

                        {{-- Refund --}}
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            @if($refundNominal > 0)
                                <span class="font-semibold text-orange-600">
                                    Rp {{ number_format($refundNominal, 0, ',', '.') }}
                                </span>

                                <p class="text-xs text-orange-400 mt-0.5">
                                    {{ $sale->refunds->sum('quantity') }} unit
                                </p>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>

                        {{-- Bersih --}}
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <span class="font-bold text-green-700">
                                Rp {{ number_format($saleNet, 0, ',', '.') }}
                            </span>
                        </td>

                        {{-- Kasir --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-yellow-400
                                            flex items-center justify-center
                                            text-xs font-bold text-gray-900">
                                    {{ strtoupper(substr($sale->user->username ?? 'U', 0, 1)) }}
                                </div>

                                <span class="text-gray-600">
                                    {{ $sale->user->username ?? '-' }}
                                </span>
                            </div>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-5 py-4 text-center">
                            <a
                                href="{{ route('sales.show', $sale) }}"
                                class="inline-flex items-center gap-1.5
                                       bg-blue-50 hover:bg-blue-100 text-blue-700
                                       px-3 py-1.5 rounded-lg text-xs
                                       font-medium transition"
                            >
                                👁️ Detail
                            </a>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="9" class="text-center py-14 text-gray-400">
                            <div class="text-5xl mb-3">📊</div>

                            <p class="font-medium">
                                Tidak ada transaksi
                            </p>

                            <p class="text-xs mt-1">
                                Tidak ditemukan transaksi pada periode yang dipilih.
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

   

@endsection