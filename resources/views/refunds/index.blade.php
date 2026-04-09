@extends('layouts.app')

@section('title', 'Refund')
@section('page-title', 'Proses Refund')
@section('page-subtitle', 'Cari transaksi untuk direfund')

@section('content')

<div class="space-y-6">

    {{-- Form Cari Transaksi --}}
    <div class="bg-white rounded-xl shadow p-5">
        <h3 class="font-semibold text-gray-800 mb-4">🔍 Cari Transaksi</h3>
        <form method="GET" action="{{ route('refunds.index') }}" class="flex gap-3">
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Masukkan ID transaksi (contoh: 1)"
                   class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">
                Cari
            </button>
            @if($search)
            <a href="{{ route('refunds.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- Daftar Transaksi --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">
                {{ $search ? 'Hasil Pencarian' : '10 Transaksi Terbaru' }}
            </h3>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($sales as $sale)

            {{-- Warna baris berbeda jika sudah direfund --}}
            <div class="p-5 transition
                        {{ $sale->refunds->count() > 0
                            ? 'bg-orange-50/50 hover:bg-orange-50'
                            : 'hover:bg-gray-50' }}">

                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">

                        {{-- Header Transaksi --}}
                        <div class="flex items-center flex-wrap gap-2 mb-2">

                            {{-- No Transaksi --}}
                            <span class="font-bold text-gray-800">
                                #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}
                            </span>

                            {{-- Metode Pembayaran --}}
                            <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full font-medium">
                                {{ $sale->payment_method_label }}
                            </span>

                            {{-- Tanggal --}}
                            <span class="text-sm text-gray-500">
                                {{ $sale->created_at->locale('id')->isoFormat('D MMM Y HH:mm') }}
                            </span>

                            {{-- Badge Sudah Direfund --}}
                            @if($sale->refunds->count() > 0)
                            <span class="inline-flex items-center gap-1.5 bg-orange-100 text-orange-700
                                         border border-orange-200 text-xs px-2.5 py-1 rounded-full font-semibold">
                                ↩️ Sudah Direfund
                                <span class="bg-orange-500 text-white text-xs w-4 h-4 rounded-full
                                             flex items-center justify-center font-bold leading-none">
                                    {{ $sale->refunds->count() }}
                                </span>
                            </span>
                            @endif

                        </div>

                        {{-- Daftar Produk --}}
                        <div class="flex flex-wrap gap-2 mb-2">
                            @foreach($sale->details as $detail)
                            @php
                                $refundedQty = $sale->refunds
                                    ->where('product_id', $detail->product_id)
                                    ->sum('quantity');
                            @endphp
                            <span class="text-xs px-2.5 py-1 rounded-lg
                                         {{ $refundedQty > 0
                                             ? 'bg-orange-100 text-orange-700 border border-orange-200'
                                             : 'bg-gray-100 text-gray-600' }}">
                                {{ $detail->product->name }}
                                <span class="font-semibold">({{ $detail->quantity }})</span>
                                @if($refundedQty > 0)
                                <span class="text-orange-500 font-semibold ml-1">
                                    -{{ $refundedQty }} ↩️
                                </span>
                                @endif
                            </span>
                            @endforeach
                        </div>

                        {{-- Info Bawah --}}
                        <div class="flex items-center flex-wrap gap-4 text-sm">
                            <span class="text-gray-500">
                                Kasir: <strong class="text-gray-700">{{ $sale->user->username }}</strong>
                            </span>
                            <span class="font-semibold text-gray-800">
                                {{ $sale->total_price_formatted }}
                            </span>
                            @if($sale->refunds->count() > 0)
                            <span class="text-orange-500 text-xs font-medium">
                                Total direfund: {{ $sale->refunds->sum('quantity') }} unit
                            </span>
                            @endif
                        </div>

                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex flex-col items-end gap-2 shrink-0">
                        <a href="{{ route('refunds.create', ['sale_id' => $sale->id]) }}"
                           class="bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200
                                  px-4 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap">
                            ↩️ Refund
                        </a>
                        @if($sale->refunds->count() > 0)
                        <a href="{{ route('refunds.show', $sale) }}"
                           class="bg-gray-50 hover:bg-gray-100 text-gray-600 border border-gray-200
                                  px-4 py-2 rounded-lg text-xs font-medium transition whitespace-nowrap">
                            📋 Lihat Riwayat
                        </a>
                        @endif
                    </div>

                </div>
            </div>

            @empty
            <div class="text-center py-12 text-gray-400">
                <div class="text-4xl mb-2">🔍</div>
                <p>Tidak ada transaksi ditemukan</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

@endsection