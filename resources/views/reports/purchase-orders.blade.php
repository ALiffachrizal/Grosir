@extends('layouts.app')

@section('title', 'Laporan Purchase Order')
@section('page-title', 'Laporan Purchase Order')
@section('page-subtitle', 'Rekapitulasi dan analisis pemesanan barang ke supplier')

@section('content')

@php
    $exportQuery = [
        'filter' => $filter,
        'status' => $status,
        'kode_supplier' => $kodeSupplier,
    ];

    if ($filter === 'custom') {
        $exportQuery['date_from'] = $dateFrom->format('Y-m-d');
        $exportQuery['date_to'] = $dateTo->format('Y-m-d');
    }
@endphp

<div class="space-y-6">

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-100">
                    ⚠️
                </div>
                <div>
                    <p class="text-sm font-semibold">Filter laporan belum valid</p>
                    <ul class="mt-1 list-inside list-disc space-y-1 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Filter Periode, Status, & Supplier --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <form
            method="GET"
            action="{{ route('reports.purchase-orders') }}"
            x-data="{
                filter: @js(old('filter', request('filter', $filter))),
                status: @js(old('status', request('status', $status))),
                supplier: @js(old('kode_supplier', request('kode_supplier', $kodeSupplier)))
            }"
        >
            <div class="flex flex-col gap-4">
                {{-- Baris Filter Periode Cepat --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Periode Tanggal
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="submit"
                                name="filter"
                                value="today"
                                @click="filter = 'today'"
                                :class="filter === 'today'
                                    ? 'bg-blue-600 text-white shadow-sm'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="rounded-xl px-4 py-2 text-sm font-semibold transition"
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
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="rounded-xl px-4 py-2 text-sm font-semibold transition"
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
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                            >
                                Tahun Ini
                            </button>

                            <button
                                type="button"
                                @click="filter = 'custom'"
                                :class="filter === 'custom'
                                    ? 'bg-blue-600 text-white shadow-sm'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                            >
                                Rentang Manual...
                            </button>
                        </div>
                    </div>

                    {{-- Tombol Export --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <a
                            href="{{ route('reports.purchase-orders.pdf', $exportQuery) }}"
                            target="_blank"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition"
                        >
                            <span>📄</span>
                            Cetak PDF
                        </a>

                        <a
                            href="{{ route('reports.purchase-orders.excel', $exportQuery) }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 transition"
                        >
                            <span>📊</span>
                            Export Excel
                        </a>
                    </div>
                </div>

                {{-- Rentang Tanggal Manual & Filter Status / Supplier --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-3 border-t border-gray-100">
                    {{-- Filter Status --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status PO</label>
                        <select
                            name="status"
                            x-model="status"
                            @change="$el.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 bg-white"
                        >
                            <option value="all">Semua Status</option>
                            <option value="pending">Menunggu (Pending)</option>
                            <option value="received">Diterima (Received)</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>

                    {{-- Filter Supplier --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Supplier</label>
                        <select
                            name="kode_supplier"
                            x-model="supplier"
                            @change="$el.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 bg-white"
                        >
                            <option value="">Semua Supplier</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->kode_supplier }}" {{ $kodeSupplier === $sup->kode_supplier ? 'selected' : '' }}>
                                    {{ $sup->name }} ({{ $sup->category->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal Awal --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Mulai</label>
                        <input
                            type="date"
                            name="date_from"
                            value="{{ old('date_from', $dateFrom->format('Y-m-d')) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 bg-white"
                        >
                    </div>

                    {{-- Tanggal Akhir --}}
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Sampai</label>
                            <input
                                type="date"
                                name="date_to"
                                value="{{ old('date_to', $dateTo->format('Y-m-d')) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 bg-white"
                            >
                        </div>
                        <input type="hidden" name="filter" :value="filter">
                        <button
                            type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition"
                        >
                            Terapkan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Ringkasan Metrik / Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Total PO --}}
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-medium">Total Purchase Order</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totalOrders }}</p>
                <p class="text-xs text-gray-400 mt-0.5">transaksi dipesan</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl font-bold">
                🛒
            </div>
        </div>

        {{-- Pending --}}
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs text-yellow-600 font-medium">Menunggu (Pending)</p>
                <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $totalPending }}</p>
                <p class="text-xs text-gray-400 mt-0.5">belum diterima</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-yellow-50 text-yellow-600 flex items-center justify-center text-2xl font-bold">
                ⏳
            </div>
        </div>

        {{-- Received --}}
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs text-green-600 font-medium">Sudah Diterima</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $totalReceived }}</p>
                <p class="text-xs text-gray-400 mt-0.5">masuk ke gudang</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-2xl font-bold">
                ✅
            </div>
        </div>

        {{-- Total Unit --}}
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 font-medium">Total Unit Barang</p>
                <p class="text-2xl font-bold text-indigo-600 mt-1">{{ number_format($totalUnits, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400 mt-0.5">total kuantitas</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl font-bold">
                📦
            </div>
        </div>

        {{-- Estimasi Pengeluaran --}}
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between sm:col-span-2 lg:col-span-1">
            <div>
                <p class="text-xs text-gray-500 font-medium">Estimasi Biaya PO</p>
                <p class="text-xl font-bold text-gray-800 mt-1">Rp {{ number_format($totalEstimatedCost, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400 mt-0.5">nilai pembelian</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold">
                💰
            </div>
        </div>
    </div>

    {{-- Tabel Data Purchase Orders --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ search: '' }">
        <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-gray-800 text-base">Daftar Transaksi Purchase Order</h3>
                <p class="text-gray-500 text-xs mt-0.5">
                    Periode {{ $dateFrom->translatedFormat('d M Y') }} - {{ $dateTo->translatedFormat('d M Y') }}
                </p>
            </div>

            <div class="w-full sm:w-72">
                <input
                    type="text"
                    x-model="search"
                    placeholder="🔍 Cari nomor PO, supplier..."
                    class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 bg-white"
                >
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="text-left px-5 py-3.5 font-medium">No. PO</th>
                        <th class="text-left px-5 py-3.5 font-medium">Tanggal</th>
                        <th class="text-left px-5 py-3.5 font-medium">Supplier</th>
                        <th class="text-left px-5 py-3.5 font-medium">Item & Kuantitas</th>
                        <th class="text-center px-5 py-3.5 font-medium">Total Unit</th>
                        <th class="text-right px-5 py-3.5 font-medium">Estimasi Nilai</th>
                        <th class="text-center px-5 py-3.5 font-medium">Status</th>
                        <th class="text-center px-5 py-3.5 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($purchaseOrders as $po)
                        <tr
                            class="hover:bg-gray-50/80 transition"
                            x-show="!search || '{{ strtolower($po->id . ' ' . ($po->supplier->name ?? '') . ' ' . $po->status) }}'.includes(search.toLowerCase())"
                        >
                            <td class="px-5 py-3.5 font-semibold text-blue-600 whitespace-nowrap">
                                #{{ $po->id }}
                            </td>
                            <td class="px-5 py-3.5 text-gray-600 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($po->order_date)->locale('id')->isoFormat('D MMM Y') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-gray-800">{{ $po->supplier->name ?? '-' }}</p>
                                <p class="text-xs text-gray-400">{{ $po->supplier->category->name ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-3.5 max-w-xs">
                                <div class="space-y-1">
                                    @foreach($po->details->take(3) as $detail)
                                        <div class="text-xs text-gray-700 flex items-center justify-between gap-2">
                                            <span class="truncate">{{ $detail->product->name ?? $detail->kode_produk }}</span>
                                            <span class="font-semibold shrink-0 text-gray-600">{{ $detail->quantity }} {{ $detail->product->base_unit ?? '' }}</span>
                                        </div>
                                    @endforeach
                                    @if($po->details->count() > 3)
                                        <p class="text-[11px] text-blue-600 font-medium italic">
                                            + {{ $po->details->count() - 3 }} produk lainnya
                                        </p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-center font-bold text-gray-800">
                                {{ $po->total_units }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold text-gray-800 whitespace-nowrap">
                                Rp {{ number_format($po->estimated_cost, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $po->status_color }}">
                                    {{ $po->status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <a
                                    href="{{ route('purchase-orders.show', $po) }}"
                                    class="inline-flex items-center gap-1 bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-medium transition"
                                >
                                    👁️ Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-gray-400">
                                <div class="text-4xl mb-2">🛒</div>
                                <p class="font-medium">Tidak ada purchase order pada periode ini</p>
                                <p class="text-xs text-gray-400 mt-1">Coba sesuaikan filter periode tanggal atau status di atas</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
