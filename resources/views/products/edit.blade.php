@extends('layouts.app')

@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk')
@section('page-subtitle', 'Perbarui data produk')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow p-6">
        <form action="{{ route('products.update', $product) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                {{-- Nama Produk --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Produk <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}"
                           class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                  {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }}"
                           placeholder="Masukkan nama produk">
                    @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select name="kode_kategori"
                            class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                {{ $errors->has('kode_kategori') ? 'border-red-400' : 'border-gray-300' }}">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($productCategories as $cat)
                            <option value="{{ $cat->kode_kategori }}" {{ old('kode_kategori', $product->kode_kategori) == $cat->kode_kategori ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('kode_kategori')
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
                        <option value="{{ $unit }}" {{ old('base_unit', $product->base_unit) == $unit ? 'selected' : '' }}>
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
                    <input type="number" name="items_per_package"
                           value="{{ old('items_per_package', $product->items_per_package) }}"
                           min="1" onfocus="this.select()"
                           class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                  {{ $errors->has('items_per_package') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('items_per_package')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Items per Bundle --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Isi per Bundle/Renceng
                    </label>
                    <input type="number" name="items_per_bundle"
                           value="{{ old('items_per_bundle', $product->items_per_bundle) }}"
                           min="1" onfocus="this.select()"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Stok Minimum --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Stok Minimum <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="minimum_stock"
                           value="{{ old('minimum_stock', $product->minimum_stock) }}"
                           min="0" onfocus="this.select()"
                           class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                  {{ $errors->has('minimum_stock') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('minimum_stock')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Stok Saat Ini (readonly) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Stok Saat Ini
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="text" value="{{ $product->stock }} {{ $product->base_unit }}"
                               readonly
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">⚠️ Stok tidak bisa diubah manual</p>
                </div>

                {{-- Harga Beli --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Harga Beli <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                        <input type="number" name="purchase_price"
                               value="{{ old('purchase_price', $product->purchase_price) }}"
                               min="0" step="100" onfocus="this.select()"
                               class="w-full pl-10 pr-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                      {{ $errors->has('purchase_price') ? 'border-red-400' : 'border-gray-300' }}">
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
                        <input type="number" name="selling_price"
                               value="{{ old('selling_price', $product->selling_price) }}"
                               min="0" step="100" onfocus="this.select()"
                               class="w-full pl-10 pr-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                      {{ $errors->has('selling_price') ? 'border-red-400' : 'border-gray-300' }}">
                    </div>
                    @error('selling_price')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Promo / Bonus Penjualan (Beli X Gratis Y) --}}
                <div x-data="{ minQty: {{ old('promo_min_qty', $product->promo_min_qty ?? 0) }}, bonusQty: {{ old('promo_bonus_qty', $product->promo_bonus_qty ?? 0) }} }"
                     class="sm:col-span-2 bg-gradient-to-r from-amber-50 to-orange-50/50 border border-amber-200 rounded-xl p-4 space-y-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-base font-bold shrink-0">
                            🎁
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-800">Promo / Bonus Penjualan (Opsional)</h4>
                            <p class="text-xs text-gray-500">Atur skema bonus "Beli X Gratis Y" (misal: Beli 10 Gratis 1). Berlaku kelipatan saat kasir.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Minimal Beli (Unit)
                            </label>
                            <input type="number" name="promo_min_qty" x-model.number="minQty"
                                   min="0" onfocus="this.select()"
                                   class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 {{ $errors->has('promo_min_qty') ? 'border-red-400' : '' }}"
                                   placeholder="0">
                            <p class="text-[11px] text-gray-400 mt-1">Kosongkan atau isi 0 jika tidak ada promo</p>
                            @error('promo_min_qty')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                Gratis Bonus (Unit)
                            </label>
                            <input type="number" name="promo_bonus_qty" x-model.number="bonusQty"
                                   min="0" onfocus="this.select()"
                                   class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 {{ $errors->has('promo_bonus_qty') ? 'border-red-400' : '' }}"
                                   placeholder="0">
                            <p class="text-[11px] text-gray-400 mt-1">Jumlah gratis yang didapat pembeli per kelipatan</p>
                            @error('promo_bonus_qty')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Live Preview --}}
                    <div x-show="minQty > 0 && bonusQty > 0"
                         class="bg-white/80 border border-amber-200/80 rounded-lg px-3 py-2 text-xs text-amber-800 flex items-center gap-2">
                        <span class="font-bold">✨ Skema Aktif:</span>
                        <span>Setiap beli <strong x-text="minQty"></strong> unit, pelanggan gratis <strong x-text="bonusQty"></strong> unit bonus (bayar <span x-text="minQty"></span>, bawa pulang <span x-text="Number(minQty) + Number(bonusQty)"></span> unit).</span>
                    </div>
                </div>

            </div>

            {{-- Tombol --}}
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                    Update Produk
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