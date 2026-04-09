@extends('layouts.app')

@section('title', 'Detail Produk')
@section('page-title', 'Detail Produk')
@section('page-subtitle', 'Informasi lengkap produk')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Info Produk --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="text-center mb-5">
                <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-3xl mx-auto mb-3">
                    📦
                </div>
                <h3 class="text-lg font-bold text-gray-800">{{ $product->name }}</h3>
                <span class="bg-blue-100 text-blue-700 text-xs px-2.5 py-1 rounded-full font-medium">
                    {{ $product->category }}
                </span>
            </div>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-gray-50">
                    <span class="text-gray-500">Satuan Dasar</span>
                    <span class="font-medium text-gray-800">{{ $product->base_unit }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-50">
                    <span class="text-gray-500">Isi per Package</span>
                    <span class="font-medium text-gray-800">{{ $product->items_per_package }} {{ $product->base_unit }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-50">
                    <span class="text-gray-500">Isi per Bundle</span>
                    <span class="font-medium text-gray-800">{{ $product->items_per_bundle }} {{ $product->base_unit }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-50">
                    <span class="text-gray-500">Harga Beli</span>
                    <span class="font-medium text-gray-800">{{ $product->purchase_price_formatted }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-50">
                    <span class="text-gray-500">Harga Jual</span>
                    <span class="font-medium text-green-600">{{ $product->selling_price_formatted }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-50">
                    <span class="text-gray-500">Stok Saat Ini</span>
                    <span class="font-bold {{ $product->stok_menipis ? 'text-red-600' : 'text-gray-800' }}">
                        {{ $product->stock }} {{ $product->base_unit }}
                    </span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-gray-500">Stok Minimum</span>
                    <span class="font-medium text-gray-800">{{ $product->minimum_stock }} {{ $product->base_unit }}</span>
                </div>
            </div>

            {{-- Status --}}
            <div class="mt-4 text-center">
                @if($product->stok_menipis)
                <span class="bg-red-100 text-red-700 text-sm px-4 py-1.5 rounded-full font-semibold">
                    ⚠️ Stok Menipis
                </span>
                @else
                <span class="bg-green-100 text-green-700 text-sm px-4 py-1.5 rounded-full font-semibold">
                    ✅ Stok Aman
                </span>
                @endif
            </div>

            {{-- Tombol --}}
            <div class="flex gap-2 mt-5">
                <a href="{{ route('products.edit', $product) }}"
                   class="flex-1 text-center bg-yellow-400 hover:bg-yellow-500 text-gray-900 py-2 rounded-lg text-sm font-semibold transition">
                    ✏️ Edit
                </a>
                <a href="{{ route('products.index') }}"
                   class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm font-medium transition">
                    ← Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- Riwayat Stok --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow">
            <div class="p-5 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">📋 Riwayat Stok Terakhir</h3>
                <p class="text-gray-500 text-sm mt-0.5">10 perubahan stok terbaru</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="text-left px-5 py-3 font-medium">Tanggal</th>
                            <th class="text-left px-5 py-3 font-medium">Tipe</th>
                            <th class="text-center px-5 py-3 font-medium">Qty</th>
                            <th class="text-left px-5 py-3 font-medium">Referensi</th>
                            <th class="text-left px-5 py-3 font-medium">User</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($product->stockLogs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-500 text-xs">
                                {{ $log->created_at->locale('id')->isoFormat('D MMM Y HH:mm') }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $log->type_color }}">
                                    {{ $log->type_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center font-bold
                                {{ $log->type === 'out' ? 'text-red-600' : 'text-green-600' }}">
                                {{ $log->type === 'out' ? '-' : '+' }}{{ $log->quantity }}
                            </td>
                            <td class="px-5 py-3 text-gray-600 text-xs">{{ $log->reference_label }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ $log->user->username ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-400">
                                <p class="text-sm">Belum ada riwayat stok</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection