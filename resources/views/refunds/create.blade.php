@extends('layouts.app')

@section('title', 'Form Refund')
@section('page-title', 'Form Refund')
@section('page-subtitle', 'Pilih produk yang akan direfund')

@section('content')

<div class="mx-auto max-w-3xl space-y-5">

    {{-- ========================================================= --}}
    {{-- PESAN ERROR SESSION --}}
    {{-- ========================================================= --}}
    @if(session('error'))
        <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-100">
                ⚠️
            </div>

            <div>
                <p class="text-sm font-semibold">
                    Refund gagal diproses
                </p>

                <p class="mt-0.5 text-sm">
                    {{ session('error') }}
                </p>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- PESAN VALIDASI --}}
    {{-- ========================================================= --}}
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-100">
                    ⚠️
                </div>

                <div>
                    <p class="text-sm font-semibold">
                        Data refund belum benar
                    </p>

                    <ul class="mt-1 list-inside list-disc space-y-1 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- INFORMASI TRANSAKSI --}}
    {{-- ========================================================= --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">

        <div class="border-b border-gray-100 bg-gradient-to-r from-slate-800 to-slate-700 px-5 py-4">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">

                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-300">
                        Transaksi Penjualan
                    </p>

                    <h3 class="mt-1 text-lg font-bold text-white">
                        #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}
                    </h3>
                </div>

                <div class="text-left sm:text-right">
                    <p class="text-xs text-slate-300">
                        Total transaksi
                    </p>

                    <p class="mt-1 text-xl font-bold text-yellow-300">
                        {{ $sale->total_price_formatted }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-3">

            {{-- Tanggal --}}
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                    Tanggal
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-700">
                    {{ $sale->created_at->locale('id')->isoFormat('D MMMM Y') }}
                </p>

                <p class="mt-0.5 text-xs text-gray-400">
                    {{ $sale->created_at->format('H:i') }} WIB
                </p>
            </div>

            {{-- Kasir --}}
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                    Kasir
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-700">
                    {{ $sale->user->username }}
                </p>
            </div>

            {{-- Metode Pembayaran --}}
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                    Pembayaran
                </p>

                <span class="mt-1 inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                    {{ $sale->payment_method_label }}
                </span>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- FORM REFUND --}}
    {{-- ========================================================= --}}
    <form action="{{ route('refunds.store') }}" method="POST">
        @csrf

        <input
            type="hidden"
            name="sale_id"
            value="{{ $sale->id }}"
        >

        <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">

            {{-- Header --}}
            <div class="flex items-start gap-3 border-b border-gray-100 p-5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-lg">
                    ↩️
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800">
                        Pilih Produk yang Direfund
                    </h3>

                    <p class="mt-0.5 text-sm text-gray-500">
                        Centang produk, kemudian tentukan jumlah barang yang dikembalikan.
                    </p>
                </div>
            </div>

            {{-- Daftar Produk --}}
            <div class="divide-y divide-gray-100">

                @foreach($refundableItems as $index => $item)

                    @php
                        $oldKodeProduk = old(
                            'items.' . $index . '.kode_produk'
                        );

                        $wasSelected =
                            $oldKodeProduk === $item['kode_produk'];

                        $oldQuantity = (int) old(
                            'items.' . $index . '.quantity',
                            $item['refundable']
                        );

                        $initialQuantity = max(
                            1,
                            min(
                                $oldQuantity,
                                $item['refundable']
                            )
                        );
                    @endphp

                    <div
                        class="p-5 transition"
                        x-data="{
                            checked: {{ $wasSelected ? 'true' : 'false' }},
                            qty: {{ $initialQuantity }}
                        }"
                        :class="checked
                            ? 'bg-orange-50/40 ring-1 ring-inset ring-orange-200'
                            : 'bg-white'"
                    >
                        <div class="flex items-start gap-4">

                            {{-- Checkbox --}}
                            <div class="pt-1">
                                <input
                                    type="checkbox"
                                    x-model="checked"
                                    class="h-5 w-5 cursor-pointer rounded border-gray-300 text-orange-500 focus:ring-orange-500"
                                >
                            </div>

                            {{-- Informasi Produk --}}
                            <div class="min-w-0 flex-1">

                                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">

                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-800">
                                            {{ $item['product']?->name ?? 'Produk tidak ditemukan' }}
                                        </p>

                                        <p class="mt-0.5 font-mono text-xs text-gray-400">
                                            {{ $item['kode_produk'] }}
                                        </p>

                                        {{-- Rincian bentuk pembelian --}}
                                        @if($item['descriptions']->isNotEmpty())
                                            <div class="mt-2 flex flex-wrap gap-1.5">
                                                @foreach($item['descriptions'] as $description)
                                                    <span class="rounded-md bg-gray-100 px-2 py-1 text-[11px] text-gray-500">
                                                        {{ $description }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="shrink-0 text-left sm:text-right">
                                        <p class="text-sm font-bold text-gray-800">
                                            {{ $item['unit_price_formatted'] }}
                                        </p>

                                        <p class="mt-0.5 text-xs text-gray-400">
                                            per unit dasar
                                        </p>
                                    </div>
                                </div>

                                {{-- Ringkasan jumlah --}}
                                <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-3">

                                    {{-- Dibeli --}}
                                    <div class="rounded-lg bg-blue-50 px-3 py-2">
                                        <p class="text-[11px] font-medium uppercase tracking-wide text-blue-500">
                                            Dibeli
                                        </p>

                                        <p class="mt-0.5 text-sm font-bold text-blue-700">
                                            {{ $item['purchased'] }}
                                            {{ $item['product']?->base_unit ?? 'unit' }}
                                        </p>
                                    </div>

                                    {{-- Sudah direfund --}}
                                    <div class="rounded-lg bg-yellow-50 px-3 py-2">
                                        <p class="text-[11px] font-medium uppercase tracking-wide text-yellow-600">
                                            Sudah Direfund
                                        </p>

                                        <p class="mt-0.5 text-sm font-bold text-yellow-700">
                                            {{ $item['refunded'] }}
                                            {{ $item['product']?->base_unit ?? 'unit' }}
                                        </p>
                                    </div>

                                    {{-- Bisa direfund --}}
                                    <div class="rounded-lg bg-green-50 px-3 py-2">
                                        <p class="text-[11px] font-medium uppercase tracking-wide text-green-600">
                                            Bisa Direfund
                                        </p>

                                        <p class="mt-0.5 text-sm font-bold text-green-700">
                                            {{ $item['refundable'] }}
                                            {{ $item['product']?->base_unit ?? 'unit' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Input jumlah refund --}}
                                <div
                                    x-show="checked"
                                    x-cloak
                                    class="mt-4 rounded-xl border border-orange-200 bg-white p-4"
                                >
                                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">

                                        <div>
                                            <label
                                                for="refund-quantity-{{ $index }}"
                                                class="text-sm font-semibold text-gray-700"
                                            >
                                                Jumlah refund
                                            </label>

                                            <p class="mt-0.5 text-xs text-gray-400">
                                                Maksimal {{ $item['refundable'] }}
                                                {{ $item['product']?->base_unit ?? 'unit' }}
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-2">

                                            {{-- Kurangi --}}
                                            <button
                                                type="button"
                                                @click="qty > 1 ? qty-- : null"
                                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-lg font-bold text-gray-700 transition hover:bg-gray-100"
                                            >
                                                −
                                            </button>

                                            {{-- Input --}}
                                            <input
                                                id="refund-quantity-{{ $index }}"
                                                type="number"
                                                name="items[{{ $index }}][quantity]"
                                                x-model.number="qty"
                                                x-bind:disabled="!checked"
                                                min="1"
                                                max="{{ $item['refundable'] }}"
                                                required
                                                @input="
                                                    if (qty < 1) qty = 1;
                                                    if (qty > {{ $item['refundable'] }}) {
                                                        qty = {{ $item['refundable'] }};
                                                    }
                                                "
                                                class="h-9 w-20 rounded-lg border border-gray-300 text-center text-sm font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                            >

                                            {{-- Tambah --}}
                                            <button
                                                type="button"
                                                @click="
                                                    qty < {{ $item['refundable'] }}
                                                        ? qty++
                                                        : null
                                                "
                                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-orange-500 text-lg font-bold text-white transition hover:bg-orange-600"
                                            >
                                                +
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Kode produk hanya aktif ketika dicentang --}}
                                <input
                                    type="hidden"
                                    name="items[{{ $index }}][kode_produk]"
                                    value="{{ $item['kode_produk'] }}"
                                    x-bind:disabled="!checked"
                                >
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Tombol --}}
            <div class="flex flex-col gap-3 border-t border-gray-100 bg-gray-50 p-5 sm:flex-row">

                <button
                    type="submit"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-orange-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-orange-600"
                >
                    ↩️ Proses Refund
                </button>

                <a
                    href="{{ route('refunds.index') }}"
                    class="inline-flex flex-1 items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                >
                    Batal
                </a>
            </div>
        </div>
    </form>

    {{-- Informasi --}}
    <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
        <div class="flex items-start gap-3">
            <span class="text-lg">ℹ️</span>

            <div>
                <p class="text-sm font-semibold text-blue-700">
                    Informasi refund
                </p>

                <p class="mt-1 text-xs leading-relaxed text-blue-600">
                    Barang yang direfund akan otomatis dikembalikan ke stok produk.
                    Jumlah refund tidak dapat melebihi jumlah barang yang dibeli.
                </p>
            </div>
        </div>
    </div>
</div>

@endsection