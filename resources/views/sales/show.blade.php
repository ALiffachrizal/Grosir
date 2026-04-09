@extends('layouts.app')

@section('title', 'Detail Penjualan')
@section('page-title', 'Detail Penjualan')
@section('page-subtitle', 'Informasi lengkap transaksi')

@section('content')

<div class="max-w-3xl mx-auto space-y-6">

    {{-- Info Transaksi --}}
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex items-start justify-between mb-5">
            <div>
                <h3 class="text-xl font-bold text-gray-800">
                    Transaksi #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}
                </h3>
                <p class="text-gray-500 text-sm mt-1">
                    {{ $sale->created_at->locale('id')->isoFormat('dddd, D MMMM Y HH:mm') }}
                </p>
            </div>
            <span class="bg-green-100 text-green-700 px-3 py-1.5 rounded-full text-sm font-semibold">
                {{ $sale->payment_method_label }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500 mb-1">Kasir</p>
                <p class="font-semibold text-gray-800">{{ $sale->user->username }}</p>
            </div>
            <div>
                <p class="text-gray-500 mb-1">Total</p>
                <p class="font-bold text-xl text-gray-900">{{ $sale->total_price_formatted }}</p>
            </div>
        </div>
    </div>

    {{-- Detail Produk --}}
    <div class="bg-white rounded-xl shadow">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Daftar Produk</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium">Produk</th>
                        <th class="text-left px-5 py-3 font-medium">Keterangan</th>
                        <th class="text-center px-5 py-3 font-medium">Qty</th>
                        <th class="text-right px-5 py-3 font-medium">Harga</th>
                        <th class="text-right px-5 py-3 font-medium">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($sale->details as $detail)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $detail->product->name }}</td>
                        <td class="px-5 py-3 text-gray-500 text-xs">{{ $detail->description ?? '-' }}</td>
                        <td class="px-5 py-3 text-center text-gray-800">{{ $detail->quantity }}</td>
                        <td class="px-5 py-3 text-right text-gray-600">{{ $detail->unit_price_formatted }}</td>
                        <td class="px-5 py-3 text-right font-bold text-gray-800">{{ $detail->subtotal_formatted }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-5 py-3 text-right font-bold text-gray-800">Total</td>
                        <td class="px-5 py-3 text-right font-bold text-lg text-gray-900">
                            {{ $sale->total_price_formatted }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Tombol --}}
    <div class="flex gap-3">
        <a href="{{ route('sales.receipt', $sale) }}"
           class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">
            🖨️ Cetak Struk
        </a>
        <a href="{{ route('sales.create') }}"
           class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">
            + Transaksi Baru
        </a>
    </div>

</div>

@endsection