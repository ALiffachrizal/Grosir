@extends('layouts.app')

@section('title', 'Tambah Produk')
@section('page-title', 'Tambah Produk')
@section('page-subtitle', 'Daftarkan produk baru')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow p-6">
        <form action="{{ route('products.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                {{-- Nama Produk --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Produk <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                  {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }}"
                           placeholder="Masukkan nama produk" autofocus>
                    @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kode Produk --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kode Produk <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="kode_produk"
                        value="{{ old('kode_produk') }}"
                        maxlength="10"
                        class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none
                                focus:ring-2 focus:ring-blue-500 uppercase
                                {{ $errors->has('kode_produk') ? 'border-red-400' : 'border-gray-300' }}"
                        placeholder="Contoh: PRD001">
                    <p class="text-xs text-gray-400 mt-1">Maksimal 10 karakter, otomatis huruf besar</p>
                    @error('kode_produk')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select name="category"
                            class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                   {{ $errors->has('category') ? 'border-red-400' : 'border-gray-300' }}">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($productCategories as $cat)
                        <option value="{{ $cat->name }}" {{ old('category') == $cat->name ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('category')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Satuan Dasar --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Satuan Dasar <span class="text-red-500">*</span>
                    </label>
                    <select name="base_unit"
                            class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                   {{ $errors->has('base_unit') ? 'border-red-400' : 'border-gray-300' }}">
                        <option value="">-- Pilih Satuan --</option>
                        @foreach($baseUnits as $unit)
                        <option value="{{ $unit }}" {{ old('base_unit') == $unit ? 'selected' : '' }}>
                            {{ $unit }}
                        </option>
                        @endforeach
                    </select>
                    @error('base_unit')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Items per Package --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Isi per Package <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="items_per_package" value="{{ old('items_per_package', 1) }}"
                           min="1"
                           class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                  {{ $errors->has('items_per_package') ? 'border-red-400' : 'border-gray-300' }}"
                           placeholder="Contoh: 40">
                    <p class="text-xs text-gray-400 mt-1">Jumlah satuan dasar dalam 1 package/dus</p>
                    @error('items_per_package')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Items per Bundle --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Isi per Bundle/Renceng
                    </label>
                    <input type="number" name="items_per_bundle" value="{{ old('items_per_bundle', 1) }}"
                           min="1"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Contoh: 10">
                    <p class="text-xs text-gray-400 mt-1">Isi 1 jika tidak ada satuan bundle</p>
                </div>

                {{-- Stok Awal --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Stok Awal
                    </label>
                    <input type="number" name="stock" value="{{ old('stock', 0) }}"
                           min="0"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="0">
                    <p class="text-xs text-gray-400 mt-1">💡 Isi jika produk sudah punya stok awal</p>
                </div>

                {{-- Stok Minimum --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Stok Minimum <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="minimum_stock" value="{{ old('minimum_stock', 0) }}"
                           min="0"
                           class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                  {{ $errors->has('minimum_stock') ? 'border-red-400' : 'border-gray-300' }}"
                           placeholder="Contoh: 10">
                    <p class="text-xs text-gray-400 mt-1">Notifikasi muncul jika stok ≤ nilai ini</p>
                    @error('minimum_stock')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Harga Beli --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Harga Beli <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                        <input type="number" name="purchase_price" value="{{ old('purchase_price') }}"
                               min="0" step="100"
                               class="w-full pl-10 pr-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                      {{ $errors->has('purchase_price') ? 'border-red-400' : 'border-gray-300' }}"
                               placeholder="0">
                    </div>
                    @error('purchase_price')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Harga Jual --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Harga Jual <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                        <input type="number" name="selling_price" value="{{ old('selling_price') }}"
                               min="0" step="100"
                               class="w-full pl-10 pr-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                      {{ $errors->has('selling_price') ? 'border-red-400' : 'border-gray-300' }}"
                               placeholder="0">
                    </div>
                    @error('selling_price')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- Tombol --}}
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                    Simpan Produk
                </button>
                <a href="{{ route('products.index') }}"
                   class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-medium transition">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

@endsection