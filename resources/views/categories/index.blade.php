@extends('layouts.app')

@section('title', 'Kategori')
@section('page-title', 'Kelola Kategori')
@section('page-subtitle', 'Manajemen kategori produk, supplier, dan satuan')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Satuan Bawaan
    |--------------------------------------------------------------------------
    |
    | Bagian ini hanya mengambil satuan bawaan asli dari konstanta Product.
    | Jangan menggunakan Product::getBaseUnits() karena fungsi tersebut sudah
    | menggabungkan satuan bawaan dan satuan tambahan dari database.
    |
    */
    $systemUnits = \App\Models\Product::BASE_UNITS_DEFAULT;

    /*
    |--------------------------------------------------------------------------
    | Total Satuan
    |--------------------------------------------------------------------------
    |
    | Total satuan merupakan jumlah satuan bawaan ditambah satuan tambahan
    | yang tersimpan pada tabel categories dengan type = unit.
    |
    */
    $totalUnits = count($systemUnits) + $unitCategories->count();
@endphp

<div class="space-y-5">

    {{-- ========================================================= --}}
    {{-- PESAN BERHASIL --}}
    {{-- ========================================================= --}}
    @if(session('success'))
        <div class="flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-700">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-green-100">
                ✅
            </div>

            <div>
                <p class="text-sm font-semibold">
                    Berhasil
                </p>

                <p class="mt-0.5 text-sm">
                    {{ session('success') }}
                </p>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- PESAN ERROR --}}
    {{-- ========================================================= --}}
    @if(session('error'))
        <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-100">
                ⚠️
            </div>

            <div>
                <p class="text-sm font-semibold">
                    Terjadi Kesalahan
                </p>

                <p class="mt-0.5 text-sm">
                    {{ session('error') }}
                </p>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- RINGKASAN DATA --}}
    {{-- ========================================================= --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        {{-- Kategori Produk --}}
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                        Kategori Produk
                    </p>

                    <p class="mt-1 text-2xl font-bold text-gray-800">
                        {{ $productCategories->count() }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        kategori terdaftar
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-xl">
                    🏷️
                </div>
            </div>
        </div>

        {{-- Kategori Supplier --}}
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                        Kategori Supplier
                    </p>

                    <p class="mt-1 text-2xl font-bold text-gray-800">
                        {{ $supplierCategories->count() }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        kategori terdaftar
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-50 text-xl">
                    🏭
                </div>
            </div>
        </div>

        {{-- Satuan Produk --}}
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
                        Satuan Produk
                    </p>

                    <p class="mt-1 text-2xl font-bold text-gray-800">
                        {{ $totalUnits }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        satuan tersedia
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-50 text-xl">
                    📏
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- KATEGORI PRODUK DAN SUPPLIER --}}
    {{-- ========================================================= --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">

        {{-- ===================================================== --}}
        {{-- KATEGORI PRODUK --}}
        {{-- ===================================================== --}}
        <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">

            {{-- Header --}}
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 p-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-lg">
                        🏷️
                    </div>

                    <div>
                        <h3 class="font-semibold text-gray-800">
                            Kategori Produk
                        </h3>

                        <p class="mt-0.5 text-xs text-gray-500">
                            Kelompok kategori untuk produk toko
                        </p>
                    </div>
                </div>

                <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                    {{ $productCategories->count() }} kategori
                </span>
            </div>

            {{-- Form Tambah Kategori Produk --}}
            <details
                class="group border-b border-gray-100"
                @if(old('type') === 'product' && $errors->any()) open @endif
            >
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 bg-gray-50 px-5 py-3.5 transition hover:bg-blue-50">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-600 font-bold text-white">
                            +
                        </span>

                        <span class="text-sm font-semibold text-gray-700">
                            Tambah Kategori Produk
                        </span>
                    </div>

                    <span class="text-gray-400 transition-transform group-open:rotate-180">
                        ▼
                    </span>
                </summary>

                <div class="bg-blue-50/30 p-5">
                    <form
                        action="{{ route('categories.store') }}"
                        method="POST"
                        class="space-y-4"
                    >
                        @csrf

                        <input type="hidden" name="type" value="product">

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                            {{-- Kode Kategori Produk --}}
                            <div>
                                <label
                                    for="product-kode-kategori"
                                    class="mb-1.5 block text-xs font-medium text-gray-600"
                                >
                                    Kode Kategori
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="product-kode-kategori"
                                    type="text"
                                    name="kode_kategori"
                                    value="{{ old('type') === 'product' ? old('kode_kategori') : '' }}"
                                    placeholder="KAT005"
                                    maxlength="10"
                                    required
                                    autocomplete="off"
                                    oninput="this.value = this.value.toUpperCase()"
                                    class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >

                                @if(old('type') === 'product')
                                    @error('kode_kategori')
                                        <p class="mt-1 text-xs text-red-500">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                @endif
                            </div>

                            {{-- Nama Kategori Produk --}}
                            <div class="sm:col-span-2">
                                <label
                                    for="product-name"
                                    class="mb-1.5 block text-xs font-medium text-gray-600"
                                >
                                    Nama Kategori
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="product-name"
                                    type="text"
                                    name="name"
                                    value="{{ old('type') === 'product' ? old('name') : '' }}"
                                    placeholder="Contoh: MAKANAN INSTAN"
                                    maxlength="100"
                                    required
                                    autocomplete="off"
                                    oninput="this.value = this.value.toUpperCase()"
                                    class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >

                                @if(old('type') === 'product')
                                    @error('name')
                                        <p class="mt-1 text-xs text-red-500">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                            <p class="text-xs text-gray-400">
                                Contoh kode: KAT001, KAT002
                            </p>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                            >
                                + Simpan Kategori
                            </button>
                        </div>
                    </form>
                </div>
            </details>

            {{-- Daftar Kategori Produk --}}
            <div class="divide-y divide-gray-100">
                @forelse($productCategories as $category)
                    <div class="flex items-center justify-between gap-4 px-5 py-3.5 transition hover:bg-gray-50">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-blue-500"></span>

                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-800">
                                    {{ $category->name }}
                                </p>

                                <p class="mt-0.5 text-xs text-gray-400">
                                    Kategori produk
                                </p>
                            </div>

                            <span class="shrink-0 rounded-md bg-gray-100 px-2 py-1 text-[11px] font-medium text-gray-500">
                                {{ $category->kode_kategori }}
                            </span>
                        </div>

                        <form
                            action="{{ route('categories.destroy', $category) }}"
                            method="POST"
                            onsubmit="return confirm('Hapus kategori {{ $category->name }}?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-500 transition hover:bg-red-50 hover:text-red-700"
                            >
                                🗑️ Hapus
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="py-10 text-center text-gray-400">
                        <div class="mb-2 text-3xl">
                            🏷️
                        </div>

                        <p class="text-sm font-medium">
                            Belum ada kategori produk
                        </p>

                        <p class="mt-1 text-xs">
                            Klik Tambah Kategori Produk untuk menambahkan.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- KATEGORI SUPPLIER --}}
        {{-- ===================================================== --}}
        <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">

            {{-- Header --}}
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 p-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-50 text-lg">
                        🏭
                    </div>

                    <div>
                        <h3 class="font-semibold text-gray-800">
                            Kategori Supplier
                        </h3>

                        <p class="mt-0.5 text-xs text-gray-500">
                            Kelompok kategori untuk pemasok
                        </p>
                    </div>
                </div>

                <span class="inline-flex items-center rounded-full bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700">
                    {{ $supplierCategories->count() }} kategori
                </span>
            </div>

            {{-- Form Tambah Kategori Supplier --}}
            <details
                class="group border-b border-gray-100"
                @if(old('type') === 'supplier' && $errors->any()) open @endif
            >
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 bg-gray-50 px-5 py-3.5 transition hover:bg-green-50">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-green-600 font-bold text-white">
                            +
                        </span>

                        <span class="text-sm font-semibold text-gray-700">
                            Tambah Kategori Supplier
                        </span>
                    </div>

                    <span class="text-gray-400 transition-transform group-open:rotate-180">
                        ▼
                    </span>
                </summary>

                <div class="bg-green-50/30 p-5">
                    <form
                        action="{{ route('categories.store') }}"
                        method="POST"
                        class="space-y-4"
                    >
                        @csrf

                        <input type="hidden" name="type" value="supplier">

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                            {{-- Kode Kategori Supplier --}}
                            <div>
                                <label
                                    for="supplier-kode-kategori"
                                    class="mb-1.5 block text-xs font-medium text-gray-600"
                                >
                                    Kode Kategori
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="supplier-kode-kategori"
                                    type="text"
                                    name="kode_kategori"
                                    value="{{ old('type') === 'supplier' ? old('kode_kategori') : '' }}"
                                    placeholder="SUP005"
                                    maxlength="10"
                                    required
                                    autocomplete="off"
                                    oninput="this.value = this.value.toUpperCase()"
                                    class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-green-500"
                                >

                                @if(old('type') === 'supplier')
                                    @error('kode_kategori')
                                        <p class="mt-1 text-xs text-red-500">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                @endif
                            </div>

                            {{-- Nama Kategori Supplier --}}
                            <div class="sm:col-span-2">
                                <label
                                    for="supplier-name"
                                    class="mb-1.5 block text-xs font-medium text-gray-600"
                                >
                                    Nama Kategori
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="supplier-name"
                                    type="text"
                                    name="name"
                                    value="{{ old('type') === 'supplier' ? old('name') : '' }}"
                                    placeholder="Contoh: DISTRIBUTOR MINUMAN"
                                    maxlength="100"
                                    required
                                    autocomplete="off"
                                    oninput="this.value = this.value.toUpperCase()"
                                    class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-green-500"
                                >

                                @if(old('type') === 'supplier')
                                    @error('name')
                                        <p class="mt-1 text-xs text-red-500">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                            <p class="text-xs text-gray-400">
                                Contoh kode: SUP001, SUP002
                            </p>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700"
                            >
                                + Simpan Kategori
                            </button>
                        </div>
                    </form>
                </div>
            </details>

            {{-- Daftar Kategori Supplier --}}
            <div class="divide-y divide-gray-100">
                @forelse($supplierCategories as $category)
                    <div class="flex items-center justify-between gap-4 px-5 py-3.5 transition hover:bg-gray-50">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-green-500"></span>

                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-800">
                                    {{ $category->name }}
                                </p>

                                <p class="mt-0.5 text-xs text-gray-400">
                                    Kategori supplier
                                </p>
                            </div>

                            <span class="shrink-0 rounded-md bg-gray-100 px-2 py-1 text-[11px] font-medium text-gray-500">
                                {{ $category->kode_kategori }}
                            </span>
                        </div>

                        <form
                            action="{{ route('categories.destroy', $category) }}"
                            method="POST"
                            onsubmit="return confirm('Hapus kategori {{ $category->name }}?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-500 transition hover:bg-red-50 hover:text-red-700"
                            >
                                🗑️ Hapus
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="py-10 text-center text-gray-400">
                        <div class="mb-2 text-3xl">
                            🏭
                        </div>

                        <p class="text-sm font-medium">
                            Belum ada kategori supplier
                        </p>

                        <p class="mt-1 text-xs">
                            Klik Tambah Kategori Supplier untuk menambahkan.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- SATUAN PRODUK --}}
    {{-- ========================================================= --}}
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">

        {{-- Header --}}
        <div class="flex flex-col justify-between gap-3 border-b border-gray-100 p-5 sm:flex-row sm:items-center">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-lg">
                    📏
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800">
                        Satuan Produk
                    </h3>

                    <p class="mt-0.5 text-xs text-gray-500">
                        Kelola satuan dasar dan satuan tambahan produk
                    </p>
                </div>
            </div>

            <span class="inline-flex self-start items-center rounded-full bg-purple-50 px-3 py-1.5 text-xs font-semibold text-purple-700 sm:self-auto">
                {{ $totalUnits }} satuan
            </span>
        </div>

        {{-- Form Tambah Satuan --}}
        <details
            class="group border-b border-gray-100"
            @if(old('type') === 'unit' && $errors->any()) open @endif
        >
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 bg-gray-50 px-5 py-3.5 transition hover:bg-purple-50">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-600 font-bold text-white">
                        +
                    </span>

                    <span class="text-sm font-semibold text-gray-700">
                        Tambah Satuan Baru
                    </span>
                </div>

                <span class="text-gray-400 transition-transform group-open:rotate-180">
                    ▼
                </span>
            </summary>

            <div class="bg-purple-50/30 p-5">
                <form
                    action="{{ route('categories.store') }}"
                    method="POST"
                    class="grid grid-cols-1 items-end gap-3 sm:grid-cols-12"
                >
                    @csrf

                    {{-- Satuan tetap disimpan pada tabel categories --}}
                    <input type="hidden" name="type" value="unit">

                    {{-- Kode Satuan --}}
                    <div class="sm:col-span-3">
                        <label
                            for="unit-kode-kategori"
                            class="mb-1.5 block text-xs font-medium text-gray-600"
                        >
                            Kode Satuan
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="unit-kode-kategori"
                            type="text"
                            name="kode_kategori"
                            value="{{ old('type') === 'unit' ? old('kode_kategori') : '' }}"
                            placeholder="SAT001"
                            maxlength="10"
                            required
                            autocomplete="off"
                            oninput="this.value = this.value.toUpperCase()"
                            class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-purple-500"
                        >

                        @if(old('type') === 'unit')
                            @error('kode_kategori')
                                <p class="mt-1 text-xs text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror
                        @endif
                    </div>

                    {{-- Nama Satuan --}}
                    <div class="sm:col-span-6">
                        <label
                            for="unit-name"
                            class="mb-1.5 block text-xs font-medium text-gray-600"
                        >
                            Nama Satuan
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="unit-name"
                            type="text"
                            name="name"
                            value="{{ old('type') === 'unit' ? old('name') : '' }}"
                            placeholder="Contoh: DUS, KODI, LUSIN"
                            maxlength="100"
                            required
                            autocomplete="off"
                            oninput="this.value = this.value.toUpperCase()"
                            class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-purple-500"
                        >

                        @if(old('type') === 'unit')
                            @error('name')
                                <p class="mt-1 text-xs text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror
                        @endif
                    </div>

                    {{-- Tombol --}}
                    <div class="sm:col-span-3">
                        <button
                            type="submit"
                            class="h-11 w-full rounded-lg bg-purple-600 text-sm font-semibold text-white transition hover:bg-purple-700"
                        >
                            + Simpan Satuan
                        </button>
                    </div>
                </form>

                <p class="mt-2 text-xs text-gray-400">
                    Contoh kode: SAT001. Nama satuan misalnya DUS, KODI,
                    LUSIN, atau GRAM.
                </p>
            </div>
        </details>

        {{-- ===================================================== --}}
        {{-- SATUAN BAWAAN --}}
        {{-- ===================================================== --}}
        <div class="p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h4 class="text-sm font-semibold text-gray-700">
                        Satuan Bawaan
                    </h4>

                    <p class="mt-0.5 text-xs text-gray-400">
                        Satuan ini tersedia otomatis dan tidak dapat dihapus
                    </p>
                </div>

                <span class="text-xs text-gray-400">
                    {{ count($systemUnits) }} satuan
                </span>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach($systemUnits as $unit)
                    <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-gray-100 bg-white text-xl shadow-sm">
                            @if($unit === 'PCS')
                                📦
                            @elseif($unit === 'BOTOL')
                                🍶
                            @elseif($unit === 'LITER')
                                💧
                            @elseif($unit === 'KG')
                                ⚖️
                            @else
                                📏
                            @endif
                        </div>

                        <div>
                            <p class="text-sm font-bold text-gray-800">
                                {{ $unit }}
                            </p>

                            <p class="mt-0.5 text-[11px] text-gray-400">
                                @if($unit === 'PCS')
                                    Pieces / Satuan
                                @elseif($unit === 'BOTOL')
                                    Botol
                                @elseif($unit === 'LITER')
                                    Liter
                                @elseif($unit === 'KG')
                                    Kilogram
                                @else
                                    Satuan produk
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- SATUAN TAMBAHAN --}}
        {{-- ===================================================== --}}
        <div class="border-t border-gray-100">

            {{-- Header Satuan Tambahan --}}
            <div class="bg-gray-50 px-5 py-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700">
                            Satuan Tambahan
                        </h4>

                        <p class="mt-0.5 text-xs text-gray-400">
                            Satuan yang ditambahkan oleh pengguna
                        </p>
                    </div>

                    <span class="text-xs text-gray-400">
                        {{ $unitCategories->count() }} satuan
                    </span>
                </div>
            </div>

            {{-- Daftar Satuan Tambahan --}}
            <div class="divide-y divide-gray-100">
                @forelse($unitCategories as $unit)
                    <div class="flex items-center justify-between gap-4 px-5 py-3.5 transition hover:bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-50 text-sm font-bold text-purple-700">
                                {{ strtoupper(substr($unit->name ?? 'S', 0, 1)) }}
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $unit->name }}
                                </p>

                                <p class="mt-0.5 text-xs text-gray-400">
                                    {{ $unit->kode_kategori }}
                                </p>
                            </div>
                        </div>

                        <form
                            action="{{ route('categories.destroy', $unit) }}"
                            method="POST"
                            onsubmit="return confirm('Hapus satuan {{ $unit->name }}?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-500 transition hover:bg-red-50 hover:text-red-700"
                            >
                                🗑️ Hapus
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="py-8 text-center text-gray-400">
                        <div class="mb-2 text-3xl">
                            📏
                        </div>

                        <p class="text-sm font-medium">
                            Belum ada satuan tambahan
                        </p>

                        <p class="mt-1 text-xs">
                            Klik Tambah Satuan Baru untuk menambahkan.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection