@extends('layouts.app')

@section('title', 'Laporan Penjualan')
@section('page-title', 'Laporan Penjualan')
@section('page-subtitle', 'Ringkasan dan detail penjualan')

@section('content')

{{-- Filter Periode --}}
<div class="bg-white rounded-xl shadow p-5 mb-6">
    <form method="GET" action="{{ route('reports.sales') }}"
          class="flex flex-wrap items-end gap-3"
          x-data="{ filter: '{{ $filter }}' }">

        {{-- Tombol Filter Cepat --}}
        <div class="flex gap-2 flex-wrap">
            <button type="submit" name="filter" value="today"
                    :class="filter === 'today' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    @click="filter = 'today'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition">
                Hari Ini
            </button>
            <button type="submit" name="filter" value="this_month"
                    :class="filter === 'this_month' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    @click="filter = 'this_month'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition">
                Bulan Ini
            </button>
            <button type="submit" name="filter" value="this_year"
                    :class="filter === 'this_year' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    @click="filter = 'this_year'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition">
                Tahun Ini
            </button>
            <button type="button"
                    :class="filter === 'custom' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    @click="filter = 'custom'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition">
                Rentang Manual
            </button>
        </div>

        {{-- Input Tanggal Manual --}}
        <div x-show="filter === 'custom'"
             class="flex items-end gap-2 flex-wrap">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Dari</label>
                <input type="date" name="date_from"
                       value="{{ request('date_from', $dateFrom?->format('Y-m-d')) }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Sampai</label>
                <input type="date" name="date_to"
                       value="{{ request('date_to', $dateTo?->format('Y-m-d')) }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" name="filter" value="custom"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                Terapkan
            </button>
        </div>

    </form>

    {{-- Info Periode --}}
    <div class="mt-3 pt-3 border-t border-gray-100">
        <p class="text-xs text-gray-500">
            Periode:
            <strong class="text-gray-700">
                {{ $dateFrom->locale('id')->isoFormat('D MMMM Y') }}
                —
                {{ $dateTo->locale('id')->isoFormat('D MMMM Y') }}
            </strong>
        </p>
    </div>
</div>

{{-- Kartu Ringkasan --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

    {{-- Total Penjualan --}}
    <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl p-5 text-white shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-200 text-sm">Total Penjualan</p>
                <p class="text-2xl font-bold mt-1">
                    Rp {{ number_format($totalSales, 0, ',', '.') }}
                </p>
                <p class="text-blue-200 text-xs mt-1">{{ $sales->count() }} transaksi</p>
            </div>
            <div class="text-4xl opacity-80">💰</div>
        </div>
    </div>

    {{-- Total Refund --}}
    <div class="bg-gradient-to-br from-orange-500 to-orange-700 rounded-xl p-5 text-white shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-orange-200 text-sm">Total Refund</p>
                <p class="text-2xl font-bold mt-1">{{ $totalRefunds }} transaksi</p>
                <p class="text-orange-200 text-xs mt-1">{{ $totalRefundQty }} unit dikembalikan</p>
            </div>
            <div class="text-4xl opacity-80">↩️</div>
        </div>
    </div>

    {{-- Net Revenue --}}
    <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-xl p-5 text-white shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-green-200 text-sm">Net Revenue</p>
                <p class="text-2xl font-bold mt-1">
                    Rp {{ number_format($netRevenue, 0, ',', '.') }}
                </p>
                <p class="text-green-200 text-xs mt-1">setelah refund</p>
            </div>
            <div class="text-4xl opacity-80">📈</div>
        </div>
    </div>

</div>

{{-- Tabel Penjualan --}}
<div class="bg-white rounded-xl shadow">

    <div class="flex items-center justify-between p-5 border-b border-gray-100">
        <div>
            <h3 class="font-semibold text-gray-800">Detail Transaksi</h3>
            <p class="text-gray-500 text-sm mt-0.5">{{ $sales->count() }} transaksi</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('reports.sales.pdf', request()->query()) }}"
               class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                📄 Export PDF
            </a>
            <a href="{{ route('reports.sales.excel', request()->query()) }}"
               class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                📊 Export Excel
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="text-left px-5 py-3 font-medium">#</th>
                    <th class="text-left px-5 py-3 font-medium">Tanggal</th>
                    <th class="text-left px-5 py-3 font-medium">Produk</th>
                    <th class="text-left px-5 py-3 font-medium">Metode</th>
                    <th class="text-right px-5 py-3 font-medium">Total</th>
                    <th class="text-center px-5 py-3 font-medium">Refund</th>
                    <th class="text-left px-5 py-3 font-medium">Kasir</th>
                    <th class="text-center px-5 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($sales as $index => $sale)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3 text-gray-500">{{ $index + 1 }}</td>
                    <td class="px-5 py-3">
                        <p class="text-gray-800">
                            {{ \Carbon\Carbon::parse($sale->date)->locale('id')->isoFormat('D MMM Y') }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ $sale->created_at->format('H:i') }} WIB
                        </p>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex flex-wrap gap-1">
                            @foreach($sale->details as $detail)
                            <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded">
                                {{ $detail->product->name }} ({{ $detail->quantity }})
                            </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">
                            {{ $sale->payment_method_label }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-800">
                        {{ $sale->total_price_formatted }}
                    </td>
                    <td class="px-5 py-3 text-center">
                        @if($sale->refunds->count() > 0)
                        <span class="bg-orange-100 text-orange-700 text-xs px-2 py-0.5 rounded-full font-medium">
                            ↩️ {{ $sale->refunds->sum('quantity') }} unit
                        </span>
                        @else
                        <span class="text-gray-300 text-xs">-</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $sale->user->username }}</td>
                    <td class="px-5 py-3 text-center">
                        <a href="{{ route('sales.show', $sale) }}"
                           class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                            👁️ Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-12 text-gray-400">
                        <div class="text-4xl mb-2">📊</div>
                        <p>Tidak ada transaksi pada periode ini</p>
                    </td>
                </tr>
                @endforelse
            </tbody>

            {{-- Total Row --}}
            @if($sales->count() > 0)
            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                <tr>
                    <td colspan="4" class="px-5 py-3 text-right font-bold text-gray-800">
                        Total
                    </td>
                    <td class="px-5 py-3 text-right font-bold text-lg text-gray-900">
                        Rp {{ number_format($totalSales, 0, ',', '.') }}
                    </td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
            @endif

        </table>
    </div>

</div>

@endsection