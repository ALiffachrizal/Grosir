@extends('layouts.app')

@section('title', 'Konfirmasi Penerimaan')
@section('page-title', 'Konfirmasi Penerimaan Barang')
@section('page-subtitle', 'Periksa dan konfirmasi penerimaan barang')

@section('content')

<div class="max-w-3xl mx-auto space-y-6">

    {{-- Info PO --}}
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex items-start justify-between mb-5">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Purchase Order #{{ $purchaseOrder->id }}</h3>
                <p class="text-gray-500 text-sm mt-1">
                    {{ \Carbon\Carbon::parse($purchaseOrder->order_date)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                </p>
            </div>
            <span class="px-3 py-1.5 rounded-full text-sm font-semibold {{ $purchaseOrder->status_color }}">
                {{ $purchaseOrder->status_label }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500 mb-1">Supplier</p>
                <p class="font-semibold text-gray-800">{{ $purchaseOrder->supplier->name }}</p>
                <p class="text-xs text-gray-400">{{ $purchaseOrder->supplier->category }}</p>
            </div>
            <div>
                <p class="text-gray-500 mb-1">Dibuat Oleh</p>
                <p class="font-semibold text-gray-800">{{ $purchaseOrder->user->username }}</p>
            </div>
        </div>
    </div>

    {{-- Detail Produk --}}
    <div class="bg-white rounded-xl shadow">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">📦 Daftar Barang yang Diterima</h3>
            <p class="text-gray-500 text-sm mt-0.5">
                Pastikan barang sudah sesuai sebelum konfirmasi
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium">#</th>
                        <th class="text-left px-5 py-3 font-medium">Produk</th>
                        <th class="text-left px-5 py-3 font-medium">Kategori</th>
                        <th class="text-center px-5 py-3 font-medium">Jumlah Diterima</th>
                        <th class="text-center px-5 py-3 font-medium">Stok Sekarang</th>
                        <th class="text-center px-5 py-3 font-medium">Stok Setelah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($purchaseOrder->details as $index => $detail)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-5 py-3 font-medium text-gray-800">
                            {{ $detail->product->name }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">
                                {{ $detail->product->category }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="font-bold text-green-600">+{{ $detail->quantity }}</span>
                            <span class="text-xs text-gray-400">{{ $detail->product->base_unit }}</span>
                        </td>
                        <td class="px-5 py-3 text-center text-gray-600">
                            {{ $detail->product->stock }}
                            <span class="text-xs text-gray-400">{{ $detail->product->base_unit }}</span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="font-bold text-blue-600">
                                {{ $detail->product->stock + $detail->quantity }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $detail->product->base_unit }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Peringatan --}}
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
        <p class="text-sm text-yellow-800 font-medium">⚠️ Perhatian</p>
        <p class="text-sm text-yellow-700 mt-1">
            Setelah dikonfirmasi, stok produk akan bertambah sesuai jumlah di atas
            dan status purchase order akan berubah menjadi <strong>Diterima</strong>.
            Tindakan ini tidak bisa dibatalkan.
        </p>
    </div>

    {{-- Tombol --}}
    <div class="flex gap-3">
        <form action="{{ route('receiving.confirm', $purchaseOrder) }}" method="POST" class="flex-1"
              onsubmit="return confirm('Konfirmasi penerimaan barang ini? Stok akan bertambah.')">
            @csrf
            <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                ✅ Konfirmasi Penerimaan
            </button>
        </form>
        <a href="{{ route('receiving.index') }}"
           class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-medium transition">
            ← Kembali
        </a>
    </div>

</div>

@endsection