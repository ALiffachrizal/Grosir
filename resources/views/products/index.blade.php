@extends('layouts.app')

@section('title', 'Produk')
@section('page-title', 'Kelola Produk')
@section('page-subtitle', 'Manajemen data produk toko')

@section('content')

<div class="bg-white rounded-xl shadow"
     x-data="{ showDeleteModal: false, deleteAction: '', deleteName: '' }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 border-b border-gray-100 gap-3">
        <div>
            <h3 class="text-gray-800 font-semibold">Daftar Produk</h3>

            <p class="text-gray-500 text-sm mt-0.5">
                Total {{ $products->count() }} produk
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">

            {{-- Form Filter --}}
            <form method="GET"
                  action="{{ route('products.index') }}"
                  class="flex flex-wrap items-center gap-2">

                {{-- Filter Kategori --}}
                <select name="category_id"
                        onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500">

                    <option value="">Semua Kategori</option>

                    @foreach($productCategories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>

                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Filter Status Stok --}}
                <select name="stock_status"
                        onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500">

                    <option value="">Semua Stok</option>

                    <option value="aman"
                        {{ request('stock_status') === 'aman' ? 'selected' : '' }}>
                        Stok Aman
                    </option>

                    <option value="menipis"
                        {{ request('stock_status') === 'menipis' ? 'selected' : '' }}>
                        Stok Menipis
                    </option>
                </select>

                {{-- Tombol Reset --}}
                @if(request()->filled('category_id') || request()->filled('stock_status'))
                    <a href="{{ route('products.index') }}"
                       class="bg-gray-100 hover:bg-gray-200 text-gray-700
                              px-3 py-2 rounded-lg text-sm font-medium transition">
                        Reset
                    </a>
                @endif
            </form>

            {{-- Tombol Tambah --}}
            <a href="{{ route('products.create') }}"
               class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700
                      text-white px-4 py-2 rounded-lg text-sm font-medium transition">

                + Tambah Produk
            </a>
        </div>
    </div>

    {{-- Informasi Filter Aktif --}}
    @if(request()->filled('category_id') || request()->filled('stock_status'))
        <div class="px-5 py-3 bg-blue-50 border-b border-blue-100">
            <p class="text-sm text-blue-700">
                Menampilkan {{ $products->count() }} produk berdasarkan filter yang dipilih.
            </p>
        </div>
    @endif

    {{-- Tabel --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">

            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="text-left px-5 py-3 font-medium">#</th>
                    <th class="text-left px-5 py-3 font-medium">Nama Produk</th>
                    <th class="text-left px-5 py-3 font-medium">Kategori</th>
                    <th class="text-left px-5 py-3 font-medium">Satuan</th>
                    <th class="text-center px-5 py-3 font-medium">Stok</th>
                    <th class="text-left px-5 py-3 font-medium">Harga Jual</th>
                    <th class="text-center px-5 py-3 font-medium">Status</th>
                    <th class="text-center px-5 py-3 font-medium">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                @forelse($products as $index => $product)

                    <tr class="hover:bg-gray-50 transition
                               {{ $product->stok_menipis ? 'bg-red-50/30' : '' }}">

                        {{-- Nomor --}}
                        <td class="px-5 py-3 text-gray-500">
                            {{ $index + 1 }}
                        </td>

                        {{-- Nama Produk --}}
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-800">
                                {{ $product->name }}
                            </p>

                            <p class="text-xs text-gray-400">
                                {{ $product->items_per_package }}
                                {{ $product->base_unit }}/Package

                                @if($product->items_per_bundle > 1)
                                    · {{ $product->items_per_bundle }}
                                    {{ $product->base_unit }}/Bundle
                                @endif
                            </p>
                        </td>

                        {{-- Kategori --}}
                        <td class="px-5 py-3">
                            <span class="bg-blue-100 text-blue-700 text-xs
                                         px-2.5 py-1 rounded-full font-medium">

                                {{ $product->category->name ?? '-' }}
                            </span>
                        </td>

                        {{-- Satuan --}}
                        <td class="px-5 py-3 text-gray-600">
                            {{ $product->base_unit }}
                        </td>

                        {{-- Stok --}}
                        <td class="px-5 py-3 text-center">
                            <span class="font-bold
                                {{ $product->stok_menipis
                                    ? 'text-red-600'
                                    : 'text-gray-800' }}">

                                {{ $product->stock }}
                            </span>

                            <p class="text-xs text-gray-400">
                                min: {{ $product->minimum_stock }}
                            </p>
                        </td>

                        {{-- Harga Jual --}}
                        <td class="px-5 py-3 text-gray-800 font-medium">
                            {{ $product->selling_price_formatted }}
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-3 text-center">

                            @if($product->stok_menipis)
                                <span class="bg-red-100 text-red-700 text-xs
                                             px-2.5 py-1 rounded-full font-semibold">
                                    ⚠️ Menipis
                                </span>
                            @else
                                <span class="bg-green-100 text-green-700 text-xs
                                             px-2.5 py-1 rounded-full font-semibold">
                                    ✅ Aman
                                </span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-center gap-2">

                                <a href="{{ route('products.show', $product) }}"
                                   class="bg-blue-50 hover:bg-blue-100 text-blue-700
                                          px-3 py-1.5 rounded-lg text-xs font-medium transition">

                                    👁️ Detail
                                </a>

                                <a href="{{ route('products.edit', $product) }}"
                                   class="bg-yellow-50 hover:bg-yellow-100 text-yellow-700
                                          px-3 py-1.5 rounded-lg text-xs font-medium transition">

                                    ✏️ Edit
                                </a>

                                <button type="button"
                                        @click="deleteAction = '{{ route('products.destroy', $product) }}'; deleteName = '{{ addslashes($product->name) }}'; showDeleteModal = true"
                                        class="bg-red-50 hover:bg-red-100 text-red-700
                                               px-3 py-1.5 rounded-lg text-xs font-medium transition">

                                    🗑️ Hapus
                                </button>

                            </div>
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="8"
                            class="text-center py-12 text-gray-400">

                            <div class="text-4xl mb-2">📦</div>

                            @if(request()->filled('category_id') || request()->filled('stock_status'))
                                <p>Tidak ada produk yang sesuai dengan filter.</p>

                                <a href="{{ route('products.index') }}"
                                   class="text-blue-500 text-sm mt-1 inline-block">

                                    Reset filter
                                </a>
                            @else
                                <p>Belum ada produk terdaftar.</p>

                                <a href="{{ route('products.create') }}"
                                   class="text-blue-500 text-sm mt-1 inline-block">

                                    + Tambah produk pertama
                                </a>
                            @endif
                        </td>
                    </tr>

                @endforelse

            </tbody>
        </table>
    </div>
    {{-- ========================================================= --}}
    {{-- FORM TERSEMBUNYI — action-nya diisi dinamis lewat Alpine --}}
    {{-- ========================================================= --}}
    <form x-ref="deleteForm" :action="deleteAction" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    {{-- ========================================================= --}}
    {{-- MODAL KONFIRMASI HAPUS --}}
    {{-- ========================================================= --}}
    <div
        x-show="showDeleteModal"
        x-cloak
        @keydown.escape.window="showDeleteModal = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;"
    >
        <div
            x-show="showDeleteModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showDeleteModal = false"
            class="absolute inset-0 bg-gray-900/50"
        ></div>

        <div
            x-show="showDeleteModal"
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
                    🗑️
                </div>

                <h3 class="text-lg font-bold text-gray-800">
                    Hapus Produk?
                </h3>

                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                    Yakin ingin menghapus
                    <span class="font-semibold text-gray-700" x-text="deleteName"></span>?
                    Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="flex gap-3 w-full mt-6">
                    <button
                        type="button"
                        @click="showDeleteModal = false"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700
                               py-2.5 rounded-xl text-sm font-semibold transition"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        @click="$refs.deleteForm.submit()"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white
                               py-2.5 rounded-xl text-sm font-semibold shadow-sm transition"
                    >
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection