@extends('layouts.app')

@section('title', 'Konfirmasi Penerimaan Barang')
@section('page-title', 'Konfirmasi Penerimaan Barang')
@section('page-subtitle', 'Periksa dan konfirmasi penerimaan barang')

@section('content')

@php
    $statusLabel = match ($purchaseOrder->status) {
        'pending'  => 'Menunggu',
        'received' => 'Diterima',
        default    => ucfirst($purchaseOrder->status),
    };
@endphp

<div class="max-w-5xl mx-auto space-y-4" x-data="{ showConfirmModal: false, showCancelModal: false }">

    {{-- ========================================================= --}}
    {{-- INFORMASI PURCHASE ORDER --}}
    {{-- ========================================================= --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        <div class="p-5">

            {{-- Judul dan Status --}}
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">

                <div>
                    <h2 class="text-2xl font-bold text-gray-800">
                        Purchase Order #{{ $purchaseOrder->id }}
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        {{ \Carbon\Carbon::parse($purchaseOrder->order_date)
                            ->locale('id')
                            ->isoFormat('dddd, D MMMM Y') }}
                    </p>
                </div>

                <span
                    class="inline-flex self-start items-center px-3 py-1.5
                           rounded-full text-xs font-semibold
                           {{ $purchaseOrder->status === 'pending'
                               ? 'bg-yellow-100 text-yellow-700'
                               : 'bg-green-100 text-green-700' }}"
                >
                    {{ $statusLabel }}
                </span>
            </div>

            {{-- Informasi PO --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4">

                {{-- Supplier --}}
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">
                        Supplier
                    </p>

                    <p class="text-base font-semibold text-gray-800">
                        {{ $purchaseOrder->supplier->name }}
                    </p>

                    <p class="text-xs text-gray-500 mt-1">
                        {{ $purchaseOrder->supplier->category->name ?? '-' }}
                    </p>
                </div>

                {{-- Dibuat Oleh --}}
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">
                        Dibuat Oleh
                    </p>

                    <p class="text-base font-semibold text-gray-800">
                        {{ $purchaseOrder->user->username }}
                    </p>
                </div>

                {{-- Total Item --}}
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">
                        Total Item
                    </p>

                    <p class="text-base font-semibold text-gray-800">
                        {{ $purchaseOrder->details->count() }} produk
                    </p>
                </div>

            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- DAFTAR BARANG --}}
    {{-- ========================================================= --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Header --}}
        <div class="p-5 border-b border-gray-100">

            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <span>📦</span>
                Daftar Barang yang Diterima
            </h3>

            <p class="text-sm text-gray-500 mt-1">
                Pastikan barang yang diterima sudah sesuai sebelum melakukan konfirmasi.
            </p>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">
                            #
                        </th>

                        <th class="px-4 py-3 text-left font-semibold">
                            Produk
                        </th>

                        <th class="px-4 py-3 text-left font-semibold">
                            Kategori
                        </th>

                        <th class="px-4 py-3 text-center font-semibold">
                            Jumlah Diterima
                        </th>

                        <th class="px-4 py-3 text-center font-semibold">
                            Stok Sekarang
                        </th>

                        <th class="px-4 py-3 text-center font-semibold">
                            Stok Setelah
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse($purchaseOrder->details as $index => $detail)

                        <tr class="hover:bg-gray-50 transition">

                            {{-- Nomor --}}
                            <td class="px-4 py-3 text-gray-500">
                                {{ $index + 1 }}
                            </td>

                            {{-- Produk --}}
                            <td class="px-4 py-3">

                                <p class="font-semibold text-gray-800">
                                    {{ $detail->product->name }}
                                </p>

                                <p class="text-xs text-gray-400 mt-0.5">
                                    Kode: {{ $detail->product->kode_produk }}
                                </p>
                            </td>

                            {{-- Kategori --}}
                            <td class="px-4 py-3">

                                <span
                                    class="inline-flex px-2.5 py-1 rounded-full
                                           text-xs font-medium bg-blue-100 text-blue-700"
                                >
                                    {{ $detail->product->category->name ?? '-' }}
                                </span>
                            </td>

                            {{-- Jumlah Diterima --}}
                            <td class="px-4 py-3 text-center whitespace-nowrap">

                                <span class="text-green-600 font-bold text-base">
                                    +{{ $detail->quantity }}
                                </span>

                                <span class="text-gray-400 text-xs ml-0.5">
                                    {{ $detail->product->base_unit }}
                                </span>
                            </td>

                            {{-- Stok Sekarang --}}
                            <td class="px-4 py-3 text-center whitespace-nowrap">

                                <span class="text-gray-700 font-semibold text-base">
                                    {{ $detail->product->stock }}
                                </span>

                                <span class="text-gray-400 text-xs ml-0.5">
                                    {{ $detail->product->base_unit }}
                                </span>
                            </td>

                            {{-- Stok Setelah --}}
                            <td class="px-4 py-3 text-center whitespace-nowrap">

                                <span class="text-blue-600 font-bold text-lg">
                                    {{ $detail->product->stock + $detail->quantity }}
                                </span>

                                <span class="text-gray-400 text-xs ml-0.5">
                                    {{ $detail->product->base_unit }}
                                </span>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-400">

                                <div class="text-4xl mb-2">
                                    📦
                                </div>

                                <p>
                                    Tidak ada barang dalam purchase order ini.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- RINGKASAN --}}
    {{-- ========================================================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

        {{-- Total Barang --}}
        <div class="bg-green-50 border border-green-100 rounded-xl p-4">

            <p class="text-xs font-medium text-green-700">
                Total Barang Diterima
            </p>

            <div class="flex items-end gap-1 mt-1">

                <p class="text-xl font-bold text-green-700">
                    {{ $purchaseOrder->details->sum('quantity') }}
                </p>

                <span class="text-xs text-green-600 mb-1">
                    unit
                </span>
            </div>
        </div>

        {{-- Jumlah Produk --}}
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">

            <p class="text-xs font-medium text-blue-700">
                Jumlah Produk
            </p>

            <div class="flex items-end gap-1 mt-1">

                <p class="text-xl font-bold text-blue-700">
                    {{ $purchaseOrder->details->count() }}
                </p>

                <span class="text-xs text-blue-600 mb-1">
                    produk
                </span>
            </div>
        </div>

        {{-- Status --}}
        <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4">

            <p class="text-xs font-medium text-yellow-700">
                Status Saat Ini
            </p>

            <p class="text-xl font-bold text-yellow-700 mt-1">
                {{ $statusLabel }}
            </p>
        </div>

    </div>

    {{-- ========================================================= --}}
    {{-- PERINGATAN --}}
    {{-- ========================================================= --}}
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">

        <div class="flex items-start gap-3">

            <div class="w-9 h-9 shrink-0 rounded-lg bg-yellow-100
                        flex items-center justify-center">
                ⚠️
            </div>

            <div>
                <h4 class="font-bold text-yellow-800 text-sm">
                    Perhatian
                </h4>

                <p class="text-sm text-yellow-700 leading-relaxed mt-1">
                    Setelah dikonfirmasi, stok produk akan bertambah sesuai
                    jumlah yang diterima dan status purchase order akan berubah
                    menjadi

                    <span class="font-semibold">
                        Diterima
                    </span>.

                    Tindakan ini tidak dapat dibatalkan.
                </p>

                <p class="text-sm text-yellow-700 leading-relaxed mt-2">
                    Jika barang ternyata tidak jadi dikirim oleh supplier,
                    gunakan tombol

                    <span class="font-semibold">
                        Batalkan PO
                    </span>

                    di bawah — bukan tombol konfirmasi.
                </p>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- TOMBOL AKSI --}}
    {{-- ========================================================= --}}
    <div class="flex flex-col sm:flex-row gap-3 pb-2">

        {{-- Form konfirmasi — tidak lagi submit langsung, tombolnya cuma buka modal --}}
        <form
            x-ref="confirmForm"
            action="{{ route('receiving.confirm', $purchaseOrder) }}"
            method="POST"
            class="flex-1"
        >
            @csrf

            <button
                type="button"
                @click="showConfirmModal = true"
                class="w-full bg-green-600 hover:bg-green-700
                       text-white py-3 rounded-xl
                       font-semibold text-sm shadow-sm transition"
            >
                ✅ Konfirmasi Penerimaan
            </button>
        </form>

        {{-- Form batal — sama, tombolnya cuma buka modal --}}
        <form
            x-ref="cancelForm"
            action="{{ route('receiving.cancel', $purchaseOrder) }}"
            method="POST"
            class="flex-1"
        >
            @csrf

            <button
                type="button"
                @click="showCancelModal = true"
                class="w-full bg-red-50 hover:bg-red-100 border border-red-200
                       text-red-600 py-3 rounded-xl
                       font-semibold text-sm transition"
            >
                ✕ Batalkan PO
            </button>
        </form>

        <a
            href="{{ route('receiving.index') }}"
            class="flex-1 flex items-center justify-center
                   bg-gray-100 hover:bg-gray-200
                   text-gray-700 py-3 rounded-xl
                   font-semibold text-sm transition"
        >
            ← Kembali
        </a>
    </div>

    {{-- ========================================================= --}}
    {{-- MODAL: KONFIRMASI PENERIMAAN --}}
    {{-- ========================================================= --}}
    <div
        x-show="showConfirmModal"
        x-cloak
        @keydown.escape.window="showConfirmModal = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;"
    >
        {{-- Backdrop --}}
        <div
            x-show="showConfirmModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showConfirmModal = false"
            class="absolute inset-0 bg-gray-900/50"
        ></div>

        {{-- Kartu modal --}}
        <div
            x-show="showConfirmModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6"
        >
            <div class="flex flex-col items-center text-center">

                <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center text-3xl mb-4">
                    ✅
                </div>

                <h3 class="text-lg font-bold text-gray-800">
                    Konfirmasi Penerimaan?
                </h3>

                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                    Stok produk akan bertambah sesuai jumlah yang diterima.
                    Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="flex gap-3 w-full mt-6">
                    <button
                        type="button"
                        @click="showConfirmModal = false"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700
                               py-2.5 rounded-xl text-sm font-semibold transition"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        @click="$refs.confirmForm.submit()"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white
                               py-2.5 rounded-xl text-sm font-semibold shadow-sm transition"
                    >
                        Ya, Konfirmasi
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- MODAL: BATALKAN PO --}}
    {{-- ========================================================= --}}
    <div
        x-show="showCancelModal"
        x-cloak
        @keydown.escape.window="showCancelModal = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;"
    >
        {{-- Backdrop --}}
        <div
            x-show="showCancelModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showCancelModal = false"
            class="absolute inset-0 bg-gray-900/50"
        ></div>

        {{-- Kartu modal --}}
        <div
            x-show="showCancelModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6"
        >
            <div class="flex flex-col items-center text-center">

                <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center text-3xl mb-4">
                    ✕
                </div>

                <h3 class="text-lg font-bold text-gray-800">
                    Batalkan Purchase Order?
                </h3>

                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                    Barang dianggap tidak jadi dikirim oleh supplier.
                    Purchase order ini akan ditandai sebagai
                    <span class="font-semibold text-gray-700">Dibatalkan</span>.
                </p>

                <div class="flex gap-3 w-full mt-6">
                    <button
                        type="button"
                        @click="showCancelModal = false"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700
                               py-2.5 rounded-xl text-sm font-semibold transition"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        @click="$refs.cancelForm.submit()"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white
                               py-2.5 rounded-xl text-sm font-semibold shadow-sm transition"
                    >
                        Ya, Batalkan
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection