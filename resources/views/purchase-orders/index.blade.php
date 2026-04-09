@extends('layouts.app')

@section('title', 'Pemesanan Barang')
@section('page-title', 'Pemesanan Barang')
@section('page-subtitle', 'Daftar semua purchase order')

@section('content')

<div class="bg-white rounded-xl shadow">

    {{-- Header --}}
    <div class="flex items-center justify-between p-5 border-b border-gray-100">
        <div>
            <h3 class="text-gray-800 font-semibold">Daftar Purchase Order</h3>
            <p class="text-gray-500 text-sm mt-0.5">Total {{ $purchaseOrders->count() }} order</p>
        </div>
        <a href="{{ route('purchase-orders.create') }}"
           class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            + Buat PO Baru
        </a>
    </div>

    {{-- Tabel --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="text-left px-5 py-3 font-medium">#</th>
                    <th class="text-left px-5 py-3 font-medium">Tanggal</th>
                    <th class="text-left px-5 py-3 font-medium">Supplier</th>
                    <th class="text-left px-5 py-3 font-medium">Dibuat Oleh</th>
                    <th class="text-center px-5 py-3 font-medium">Jumlah Produk</th>
                    <th class="text-center px-5 py-3 font-medium">Status</th>
                    <th class="text-center px-5 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($purchaseOrders as $index => $po)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3 text-gray-500">{{ $index + 1 }}</td>
                    <td class="px-5 py-3 text-gray-600">
                        {{ \Carbon\Carbon::parse($po->order_date)->locale('id')->isoFormat('D MMM Y') }}
                    </td>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $po->supplier->name }}</p>
                        <p class="text-xs text-gray-400">{{ $po->supplier->category }}</p>
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $po->user->username }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="font-semibold text-gray-800">{{ $po->details->count() }}</span>
                        <span class="text-gray-400 text-xs">produk</span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $po->status_color }}">
                            {{ $po->status_label }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <a href="{{ route('purchase-orders.show', $po) }}"
                           class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                            👁️ Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-12 text-gray-400">
                        <div class="text-4xl mb-2">🛒</div>
                        <p>Belum ada purchase order</p>
                        <a href="{{ route('purchase-orders.create') }}"
                           class="text-blue-500 text-sm mt-1 inline-block">
                            + Buat PO pertama
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection