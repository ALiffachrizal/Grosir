@extends('layouts.app')

@section('title', 'Kategori')
@section('page-title', 'Kelola Kategori')
@section('page-subtitle', 'Manajemen kategori produk dan supplier')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Panel Kategori Produk --}}
    <div class="bg-white rounded-xl shadow">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">🏷️ Kategori Produk</h3>
            <p class="text-gray-500 text-sm mt-0.5">{{ $productCategories->count() }} kategori</p>
        </div>

        {{-- Form Tambah --}}
        <div class="p-5 border-b border-gray-100 bg-gray-50">
            <form action="{{ route('categories.store') }}" method="POST" class="flex gap-3">
                @csrf
                <input type="hidden" name="type" value="product">
                <input type="text" name="name"
                       value="{{ old('name') }}"
                       placeholder="Nama kategori produk..."
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    + Tambah
                </button>
            </form>
        </div>

        {{-- Daftar Kategori --}}
        <div class="divide-y divide-gray-50">
            @forelse($productCategories as $category)
            <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                    <span class="text-sm text-gray-800">{{ $category->name }}</span>
                </div>
                <form action="{{ route('categories.destroy', $category) }}" method="POST"
                      onsubmit="return confirm('Hapus kategori {{ $category->name }}?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="text-red-400 hover:text-red-600 text-xs hover:bg-red-50 px-2 py-1 rounded transition">
                        🗑️ Hapus
                    </button>
                </form>
            </div>
            @empty
            <div class="text-center py-8 text-gray-400">
                <p class="text-sm">Belum ada kategori produk</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Panel Kategori Supplier --}}
    <div class="bg-white rounded-xl shadow">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">🏭 Kategori Supplier</h3>
            <p class="text-gray-500 text-sm mt-0.5">{{ $supplierCategories->count() }} kategori</p>
        </div>

        {{-- Form Tambah --}}
        <div class="p-5 border-b border-gray-100 bg-gray-50">
            <form action="{{ route('categories.store') }}" method="POST" class="flex gap-3">
                @csrf
                <input type="hidden" name="type" value="supplier">
                <input type="text" name="name"
                       value="{{ old('name') }}"
                       placeholder="Nama kategori supplier..."
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    + Tambah
                </button>
            </form>
        </div>

        {{-- Daftar Kategori --}}
        <div class="divide-y divide-gray-50">
            @forelse($supplierCategories as $category)
            <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full bg-green-400"></span>
                    <span class="text-sm text-gray-800">{{ $category->name }}</span>
                </div>
                <form action="{{ route('categories.destroy', $category) }}" method="POST"
                      onsubmit="return confirm('Hapus kategori {{ $category->name }}?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="text-red-400 hover:text-red-600 text-xs hover:bg-red-50 px-2 py-1 rounded transition">
                        🗑️ Hapus
                    </button>
                </form>
            </div>
            @empty
            <div class="text-center py-8 text-gray-400">
                <p class="text-sm">Belum ada kategori supplier</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

@endsection