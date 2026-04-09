@extends('layouts.app')

@section('title', 'Detail Refund')
@section('page-title', 'Detail Refund')
@section('page-subtitle', 'Riwayat refund transaksi')

@section('content')

<div class="max-w-3xl mx-auto space-y-6">

    {{-- Info Transaksi --}}
    <div class="bg-white rounded-xl shadow p-5">
        <h3 class="font-bold text-gray-800 text-lg mb-4">
            Transaksi #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}
        </h3>
        <div class="grid grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Tanggal</p>
                <p class="font-semibold">{{ $sale->created_at->locale('id')->isoFormat('D MMM Y') }}</p>
            </div>
            <div>
                <p class="text-gray-500">Kasir</p>
                <p class="font-semibold">{{ $sale->user->username }}</p>
            </div>
            <div>
                <p class="text-gray-500">Total</p>
                <p class="font-semibold">{{ $sale->total_price_formatted }}</p>
            </div>
        </div>
    </div>

    {{-- Riwayat Refund --}}
    <div class="bg-white rounded-xl shadow">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Riwayat Refund</h3>
            <p class="text-gray-500 text-sm mt-0.5">{{ $sale->refunds->count() }} refund diproses</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium">Produk</th>
                        <th class="text-center px-5 py-3 font-medium">Qty Refund</th>
                        <th class="text-left px-5 py-3 font-medium">Tanggal</th>
                        <th class="text-left px-5 py-3 font-medium">Diproses Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sale->refunds as $refund)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">
                            {{ $refund->product->name }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="font-bold text-orange-600">{{ $refund->quantity }}</span>
                        </td>
                        <td class="px-5 py-3 text-gray-500">
                            {{ \Carbon\Carbon::parse($refund->date)->locale('id')->isoFormat('D MMM Y') }}
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $refund->user->username }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-8 text-gray-400">
                            Belum ada refund untuk transaksi ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tombol --}}
    <div class="flex gap-3">
        <a href="{{ route('refunds.create', ['sale_id' => $sale->id]) }}"
           class="flex-1 text-center bg-orange-500 hover:bg-orange-600 text-white py-2.5 rounded-lg text-sm font-semibold transition">
            ↩️ Refund Lagi
        </a>
        <a href="{{ route('refunds.index') }}"
           class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-medium transition">
            ← Kembali
        </a>
    </div>

</div>

@endsection