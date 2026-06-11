@extends('layouts.app')

@section('title', 'Stock Log')
@section('page-title', 'Stock Log')
@section('page-subtitle', 'Riwayat semua perubahan stok')

@section('content')

{{-- Filter --}}
<div class="bg-white rounded-xl shadow p-5 mb-6">
    <form action="{{ route('stock-logs.index') }}"
          method="GET"
          class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-12 gap-4 items-end">

        {{-- Cari Produk --}}
        <div class="xl:col-span-4">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Cari Produk
            </label>

            <input type="text"
                   name="product_search"
                   value="{{ request('product_search') }}"
                   placeholder="Masukkan nama atau kode produk..."
                   class="w-full h-11 px-4 border border-gray-300 rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Tipe --}}
        <div class="xl:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Tipe
            </label>

            <select name="type"
                    class="w-full h-11 px-4 border border-gray-300 rounded-lg text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Tipe</option>
                <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>
                    Masuk
                </option>
                <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>
                    Keluar
                </option>
                <option value="refund" {{ request('type') === 'refund' ? 'selected' : '' }}>
                    Refund
                </option>
            </select>
        </div>

        {{-- Dari Tanggal --}}
        <div class="xl:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Dari Tanggal
            </label>

            <input type="date"
                   name="date_from"
                   value="{{ request('date_from') }}"
                   class="w-full h-11 px-4 border border-gray-300 rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Sampai Tanggal --}}
        <div class="xl:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Sampai Tanggal
            </label>

            <input type="date"
                   name="date_to"
                   value="{{ request('date_to') }}"
                   class="w-full h-11 px-4 border border-gray-300 rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Tombol --}}
        <div class="xl:col-span-2 flex gap-2">
            <button type="submit"
                    class="flex-1 h-11 bg-blue-600 hover:bg-blue-700 text-white
                           rounded-lg text-sm font-semibold transition">
                🔍 Filter
            </button>

            <a href="{{ route('stock-logs.index') }}"
               class="flex-1 h-11 flex items-center justify-center bg-gray-100
                      hover:bg-gray-200 text-gray-700 rounded-lg text-sm
                      font-medium transition">
                Reset
            </a>
        </div>

        {{-- Informasi Filter Aktif --}}
        @if(
            request()->filled('product_search') ||
            request()->filled('type') ||
            request()->filled('date_from') ||
            request()->filled('date_to')
        )
            <div class="xl:col-span-12 pt-3 border-t border-gray-100">
                <div class="flex flex-wrap items-center gap-2 text-sm text-blue-600">
                    <span>✅ Filter aktif</span>
                    <span class="text-gray-400">—</span>
                    <span>{{ $logs->total() }} hasil ditemukan</span>
                </div>
            </div>
        @endif

    </form>
</div>

{{-- Tabel --}}
<div class="bg-white rounded-xl shadow">

    {{-- Header --}}
    <div class="flex items-center justify-between p-5 border-b border-gray-100">
        <div>
            <h3 class="font-semibold text-gray-800">Riwayat Perubahan Stok</h3>
            <p class="text-gray-500 text-sm mt-0.5">
                Total {{ $logs->total() }} log
            </p>
        </div>

        <div class="flex gap-2">
            <span class="bg-green-100 text-green-700 text-xs px-3 py-1.5 rounded-full font-medium">
                ↑ Masuk
            </span>
            <span class="bg-red-100 text-red-700 text-xs px-3 py-1.5 rounded-full font-medium">
                ↓ Keluar
            </span>
            <span class="bg-yellow-100 text-yellow-700 text-xs px-3 py-1.5 rounded-full font-medium">
                ↩ Refund
            </span>
        </div>
    </div>

    {{-- Tabel Data --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="text-left px-5 py-3 font-medium">Tanggal</th>
                    <th class="text-left px-5 py-3 font-medium">Produk</th>
                    <th class="text-center px-5 py-3 font-medium">Tipe</th>
                    <th class="text-center px-5 py-3 font-medium">Qty</th>
                    <th class="text-left px-5 py-3 font-medium">Referensi</th>
                    <th class="text-left px-5 py-3 font-medium">Catatan</th>
                    <th class="text-left px-5 py-3 font-medium">User</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 transition">

                        {{-- Tanggal --}}
                        <td class="px-5 py-3">
                            <p class="text-gray-800 text-xs font-medium">
                                {{ $log->created_at->locale('id')->isoFormat('D MMM Y') }}
                            </p>
                            <p class="text-gray-400 text-xs">
                                {{ $log->created_at->format('H:i') }} WIB
                            </p>
                        </td>

                        {{-- Produk --}}
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-800">
                                {{ $log->product->name ?? '-' }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ $log->product->category->name ?? '-' }}
                            </p>
                        </td>

                        {{-- Tipe Badge --}}
                        <td class="px-5 py-3 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $log->type_color }}">
                                @if($log->type === 'in')
                                    ↑
                                @elseif($log->type === 'out')
                                    ↓
                                @else
                                    ↩
                                @endif
                                {{ $log->type_label }}
                            </span>
                        </td>

                        {{-- Qty --}}
                        <td class="px-5 py-3 text-center">
                            <span class="font-bold text-lg
                                {{ $log->type === 'out'
                                    ? 'text-red-600'
                                    : ($log->type === 'in' ? 'text-green-600' : 'text-yellow-600') }}">
                                {{ $log->type === 'out' ? '-' : '+' }}{{ $log->quantity }}
                            </span>
                            <p class="text-xs text-gray-400">
                                {{ $log->product->base_unit ?? '' }}
                            </p>
                        </td>

                        {{-- Referensi --}}
                        <td class="px-5 py-3">
                            <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-lg">
                                {{ $log->reference_label }}
                            </span>
                        </td>

                        {{-- Catatan --}}
                        <td class="px-5 py-3 text-gray-500 text-xs max-w-xs truncate">
                            {{ $log->note ?? '-' }}
                        </td>

                        {{-- User --}}
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-yellow-400 flex items-center justify-center
                                            text-gray-900 font-bold text-xs">
                                    {{ strtoupper(substr($log->user->username ?? 'S', 0, 1)) }}
                                </div>
                                <span class="text-gray-600 text-xs">
                                    {{ $log->user->username ?? '-' }}
                                </span>
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-gray-400">
                            <div class="text-4xl mb-2">📋</div>
                            <p>Belum ada riwayat perubahan stok</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $logs->links() }}
        </div>
    @endif

</div>

@endsection