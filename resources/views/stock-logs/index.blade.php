@extends('layouts.app')

@section('title', 'Stock Log')
@section('page-title', 'Stock Log')
@section('page-subtitle', 'Riwayat semua perubahan stok')

@section('content')

{{-- Filter --}}
<div class="bg-white rounded-xl shadow p-5 mb-6">
    <form method="GET" action="{{ route('stock-logs.index') }}"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">

        {{-- Filter Produk --}}
        <div class="lg:col-span-2">
            <label class="block text-xs font-medium text-gray-500 mb-1">Produk</label>
            <select name="product_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Produk</option>
                @foreach($products as $product)
                <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                    {{ $product->name }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Filter Tipe --}}
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Tipe</label>
            <select name="type"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Tipe</option>
                <option value="in"     {{ request('type') === 'in'     ? 'selected' : '' }}>Masuk</option>
                <option value="out"    {{ request('type') === 'out'    ? 'selected' : '' }}>Keluar</option>
                <option value="refund" {{ request('type') === 'refund' ? 'selected' : '' }}>Refund</option>
            </select>
        </div>

        {{-- Filter Tanggal Dari --}}
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Filter Tanggal Sampai --}}
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Tombol --}}
        <div class="lg:col-span-5 flex gap-2">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
                🔍 Filter
            </button>
            <a href="{{ route('stock-logs.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-lg text-sm font-medium transition">
                Reset
            </a>

            {{-- Info hasil filter --}}
            @if(request()->hasAny(['product_id', 'type', 'date_from', 'date_to']))
            <span class="flex items-center text-xs text-blue-600 font-medium">
                ✅ Filter aktif — {{ $logs->total() }} hasil
            </span>
            @endif
        </div>

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

        {{-- Summary Badge --}}
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
                        <p class="font-medium text-gray-800">{{ $log->product->name }}</p>
                        <p class="text-xs text-gray-400">{{ $log->product->category }}</p>
                    </td>

                    {{-- Tipe Badge --}}
                    <td class="px-5 py-3 text-center">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $log->type_color }}">
                            @if($log->type === 'in') ↑
                            @elseif($log->type === 'out') ↓
                            @else ↩
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
                        <p class="text-xs text-gray-400">{{ $log->product->base_unit }}</p>
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
                            <span class="text-gray-600 text-xs">{{ $log->user->username ?? '-' }}</span>
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