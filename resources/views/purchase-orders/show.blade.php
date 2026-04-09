@extends('layouts.app')

@section('title', 'Detail Purchase Order')
@section('page-title', 'Detail Purchase Order')
@section('page-subtitle', 'Informasi lengkap purchase order')

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
            <div>
                <p class="text-gray-500 mb-1">Tanggal Order</p>
                <p class="font-semibold text-gray-800">
                    {{ \Carbon\Carbon::parse($purchaseOrder->order_date)->locale('id')->isoFormat('D MMMM Y') }}
                </p>
            </div>
            <div>
                <p class="text-gray-500 mb-1">Status</p>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $purchaseOrder->status_color }}">
                    {{ $purchaseOrder->status_label }}
                </span>
            </div>
        </div>
    </div>

    {{-- Detail Produk --}}
    <div class="bg-white rounded-xl shadow">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Daftar Produk</h3>
            <p class="text-gray-500 text-sm mt-0.5">{{ $purchaseOrder->details->count() }} produk dipesan</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium">#</th>
                        <th class="text-left px-5 py-3 font-medium">Produk</th>
                        <th class="text-left px-5 py-3 font-medium">Kategori</th>
                        <th class="text-center px-5 py-3 font-medium">Jumlah</th>
                        <th class="text-left px-5 py-3 font-medium">Satuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($purchaseOrder->details as $index => $detail)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $detail->product->name }}</td>
                        <td class="px-5 py-3">
                            <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">
                                {{ $detail->product->category }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center font-bold text-gray-800">{{ $detail->quantity }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $detail->product->base_unit }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

        {{-- Tombol Aksi --}}
    <div class="flex gap-3">
        @if($purchaseOrder->status === 'pending')
        <a href="{{ route('receiving.show', $purchaseOrder) }}"
        class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">
            📥 Proses Penerimaan
        </a>
        @endif
        <a href="{{ route('purchase-orders.index') }}"
        class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-medium transition">
            ← Kembali
        </a>
    </div>

</div>

@endsection