@extends('layouts.app')

@section('title', 'Produk')
@section('page-title', 'Kelola Produk')
@section('page-subtitle', 'Manajemen data produk toko')

@section('content')

<div class="bg-white rounded-xl shadow">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 border-b border-gray-100 gap-3">
        <div>
            <h3 class="text-gray-800 font-semibold">Daftar Produk</h3>
            <p class="text-gray-500 text-sm mt-0.5">Total {{ $products->count() }} produk</p>
        </div>
        <div class="flex flex-wrap gap-2">
            {{-- Filter Kategori --}}
            <form method="GET" action="{{ route('products.index') }}" class="flex gap-2">
                <select name="category" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Kategori</option>
                    @foreach($productCategories as $cat)
                    <option value="{{ $cat->name }}" {{ request('category') == $cat->name ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
                {{-- Filter Stok Menipis --}}
                <select name="low_stock" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Stok</option>
                    <option value="1" {{ request('low_stock') ? 'selected' : '' }}>Stok Menipis</option>
                </select>
            </form>
            <a href="{{ route('products.create') }}"
               class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                + Tambah Produk
            </a>
        </div>
    </div>

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
                <tr class="hover:bg-gray-50 transition {{ $product->stok_menipis ? 'bg-red-50/30' : '' }}">
                    <td class="px-5 py-3 text-gray-500">{{ $index + 1 }}</td>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $product->name }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $product->items_per_package }} {{ $product->base_unit }}/Package
                            @if($product->items_per_bundle > 1)
                            · {{ $product->items_per_bundle }} {{ $product->base_unit }}/Bundle
                            @endif
                        </p>
                    </td>
                    <td class="px-5 py-3">
                        <span class="bg-blue-100 text-blue-700 text-xs px-2.5 py-1 rounded-full font-medium">
                            {{ $product->category }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $product->base_unit }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="font-bold {{ $product->stok_menipis ? 'text-red-600' : 'text-gray-800' }}">
                            {{ $product->stock }}
                        </span>
                        <p class="text-xs text-gray-400">min: {{ $product->minimum_stock }}</p>
                    </td>
                    <td class="px-5 py-3 text-gray-800 font-medium">
                        {{ $product->selling_price_formatted }}
                    </td>
                    <td class="px-5 py-3 text-center">
                        @if($product->stok_menipis)
                        <span class="bg-red-100 text-red-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                            ⚠️ Menipis
                        </span>
                        @else
                        <span class="bg-green-100 text-green-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                            ✅ Aman
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('products.show', $product) }}"
                               class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                👁️ Detail
                            </a>
                            <a href="{{ route('products.edit', $product) }}"
                               class="bg-yellow-50 hover:bg-yellow-100 text-yellow-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                ✏️ Edit
                            </a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST"
                                  onsubmit="return confirm('Hapus produk {{ $product->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="bg-red-50 hover:bg-red-100 text-red-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                    🗑️ Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-12 text-gray-400">
                        <div class="text-4xl mb-2">📦</div>
                        <p>Belum ada produk terdaftar</p>
                        <a href="{{ route('products.create') }}" class="text-blue-500 text-sm mt-1 inline-block">
                            + Tambah produk pertama
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection