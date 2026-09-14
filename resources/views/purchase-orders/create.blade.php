@extends('layouts.app')

@section('title', 'Buat Purchase Order')
@section('page-title', 'Buat Purchase Order')
@section('page-subtitle', 'Pemesanan barang ke supplier dengan validasi batas minimal stok')

@section('content')

<div
    class="space-y-6"
    x-data="purchaseOrderApp(@js($suppliers), @js($products), @js($prefillProduct), @js($prefillSupplier))"
    x-init="init()"
>
    {{-- Form Utama --}}
    <form
        action="{{ route('purchase-orders.store') }}"
        method="POST"
        @submit="prepareSubmit($event)"
    >
        @csrf

        {{-- Hidden input container untuk submit array produk --}}
        <div id="hidden-inputs"></div>

        {{-- Header Form: Supplier & Tanggal Order --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Supplier --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Pilih Supplier <span class="text-red-500">*</span>
                    </label>

                    <select
                        name="kode_supplier"
                        x-model="selectedSupplierId"
                        @change="onSupplierChange()"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $supplier)
                            <option
                                value="{{ $supplier->kode_supplier }}"
                                {{ old('kode_supplier', $prefillSupplier?->kode_supplier) === $supplier->kode_supplier ? 'selected' : '' }}
                            >
                                {{ $supplier->name }} (Kategori: {{ $supplier->category->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>

                    @error('kode_supplier')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror

                    {{-- Info Kategori Supplier Terpilih --}}
                    <div x-show="currentSupplier" x-cloak class="mt-2 text-xs text-gray-500 flex items-center gap-2">
                        <span>📦 Kategori: <strong class="text-gray-800" x-text="currentSupplier?.category?.name || '-'"></strong></span>
                        <span>•</span>
                        <span>📞 Telp: <strong class="text-gray-800" x-text="currentSupplier?.phone || '-'"></strong></span>
                    </div>
                </div>

                {{-- Tanggal Order --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Tanggal Order <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="date"
                        name="order_date"
                        x-model="orderDate"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >

                    @error('order_date')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror

                    <p class="mt-2 text-xs text-gray-400">Tanggal pengajuan pemesanan barang</p>
                </div>
            </div>
        </div>

        {{-- Alert Error Server-Side jika ada --}}
        @if($errors->has('products') || $errors->has('products.*') || $errors->has('products.*.quantity'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
                <div class="flex items-start gap-3">
                    <span class="text-xl">⚠️</span>
                    <div>
                        <p class="font-bold text-sm">Pesanan belum memenuhi syarat:</p>
                        <ul class="mt-1 list-disc list-inside text-xs space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Status Sebelum Supplier Dipilih --}}
        <div
            x-show="!selectedSupplierId"
            x-cloak
            class="bg-yellow-50 border border-yellow-200 rounded-2xl p-8 text-center text-yellow-800 my-6"
        >
            <div class="text-4xl mb-2">🏢</div>
            <h4 class="font-bold text-base">Silakan Pilih Supplier Terlebih Dahulu</h4>
            <p class="text-xs text-yellow-700 mt-1 max-w-md mx-auto">
                Setelah memilih supplier, sistem akan otomatis menampilkan katalog produk yang sesuai dengan kategori supplier tersebut.
            </p>
        </div>

        {{-- Workspace Pemesanan (Tampil jika Supplier sudah dipilih) --}}
        <div x-show="selectedSupplierId" x-cloak class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- PANEL KIRI: KATALOG & PENCARIAN PRODUK CEPAT (col 5) --}}
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm">Pilih Produk Supplier</h3>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Kategori: <span class="font-semibold text-blue-600" x-text="currentSupplier?.category?.name || '-'"></span>
                            </p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700" x-text="supplierProducts.length + ' produk'"></span>
                    </div>

                    {{-- Tombol Aksi Cepat: Pesan Semua Stok Menipis --}}
                    <template x-if="lowStockSupplierProducts.length > 0">
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <p class="text-xs font-bold text-red-800">
                                        ⚠️ Ada <span x-text="lowStockSupplierProducts.length"></span> produk stok menipis!
                                    </p>
                                    <p class="text-[11px] text-red-600 mt-0.5">
                                        Perlu segera dipesan ke supplier ini.
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="addAllLowStock()"
                                    class="shrink-0 bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm transition inline-flex items-center gap-1"
                                >
                                    <span>Pesan Semua</span>
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- Filter & Pencarian Cepat --}}
                    <div class="space-y-2 mb-3">
                        <input
                            type="text"
                            x-model="productSearch"
                            placeholder="🔍 Cari nama atau kode produk..."
                            class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 bg-white"
                        >

                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="productFilter = 'all'"
                                :class="productFilter === 'all' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                class="flex-1 py-1.5 rounded-lg text-xs font-semibold transition text-center"
                            >
                                Semua (<span x-text="supplierProducts.length"></span>)
                            </button>
                            <button
                                type="button"
                                @click="productFilter = 'low_stock'"
                                :class="productFilter === 'low_stock' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100'"
                                class="flex-1 py-1.5 rounded-lg text-xs font-semibold transition text-center"
                            >
                                ⚠️ Menipis (<span x-text="lowStockSupplierProducts.length"></span>)
                            </button>
                        </div>
                    </div>

                    {{-- Daftar Produk Tersedia --}}
                    <div class="divide-y divide-gray-100 max-h-[520px] overflow-y-auto pr-1">
                        <template x-for="product in filteredCatalog" :key="product.kode_produk">
                            <div class="py-3 flex items-center justify-between gap-3 hover:bg-gray-50/80 px-2 rounded-xl transition">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5">
                                        <p class="text-xs font-bold text-gray-800 truncate" x-text="product.name"></p>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mt-1 text-[11px]">
                                        {{-- Indikator Stok Saat Ini (Warna Merah jika Menipis) --}}
                                        <template x-if="Number(product.stock) <= Number(product.minimum_stock)">
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-red-100 text-red-700 font-bold border border-red-200">
                                                ⚠️ Stok: <span x-text="product.stock + ' ' + product.base_unit"></span> (Menipis)
                                            </span>
                                        </template>
                                        <template x-if="Number(product.stock) > Number(product.minimum_stock)">
                                            <span class="text-gray-500">
                                                Stok: <strong class="text-gray-700" x-text="product.stock + ' ' + product.base_unit"></strong>
                                            </span>
                                        </template>

                                        {{-- Informasi Minimal Stok --}}
                                        <span class="text-gray-400">•</span>
                                        <span class="text-gray-600">
                                            Min. Stok: <strong class="text-gray-800" x-text="product.minimum_stock + ' ' + product.base_unit"></strong>
                                        </span>
                                    </div>

                                    <p class="text-[11px] text-gray-400 mt-0.5">
                                        Harga Beli: <span class="font-medium text-gray-700" x-text="formatRupiah(product.purchase_price)"></span>
                                    </p>
                                </div>

                                {{-- Tombol Tambah ke PO --}}
                                <div class="shrink-0">
                                    <template x-if="!isItemInOrder(product.kode_produk)">
                                        <button
                                            type="button"
                                            @click="addItem(product)"
                                            class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm transition"
                                        >
                                            <span>+</span>
                                            <span>Pesan</span>
                                        </button>
                                    </template>
                                    <template x-if="isItemInOrder(product.kode_produk)">
                                        <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-bold px-2.5 py-1 rounded-lg">
                                            ✓ Dipesan
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="filteredCatalog.length === 0">
                            <div class="py-8 text-center text-gray-400 text-xs">
                                <span class="text-2xl block mb-1">🔍</span>
                                Tidak ada produk yang sesuai kriteria pencarian.
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- PANEL KANAN: DAFTAR PESANAN PO / KERANJANG (col 7) --}}
            <div class="lg:col-span-7 space-y-4">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                        <div>
                            <h3 class="font-bold text-gray-800 text-base">Daftar Produk yang Dipesan</h3>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Aturan: Jumlah pesanan setiap produk harus memenuhi batas minimal stok.
                            </p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-600 text-white" x-text="items.length + ' Produk'"></span>
                    </div>

                    {{-- Empty State jika belum ada produk di PO --}}
                    <div x-show="items.length === 0" class="py-12 text-center text-gray-400">
                        <div class="text-4xl mb-2">🛒</div>
                        <h4 class="font-semibold text-gray-700 text-sm">Belum Ada Produk Dipilih</h4>
                        <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                            Pilih produk dari daftar di sebelah kiri dengan mengklik tombol <strong>"+ Pesan"</strong> atau gunakan <strong>"Pesan Semua"</strong> untuk stok menipis.
                        </p>
                    </div>

                    {{-- Daftar Item yang Dipesan --}}
                    <div x-show="items.length > 0" class="space-y-4">
                        <template x-for="(item, index) in items" :key="item.kode_produk">
                            <div
                                class="rounded-2xl border p-4 transition"
                                :class="isInvalidQuantity(item) ? 'border-red-400 bg-red-50/30' : 'border-gray-200 bg-white hover:border-gray-300'"
                            >
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5" x-text="index + 1"></div>
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-800" x-text="item.name"></h4>
                                            <div class="flex flex-wrap items-center gap-2 text-xs mt-0.5">
                                                {{-- Stok saat ini --}}
                                                <template x-if="Number(item.stock) <= Number(item.minimum_stock)">
                                                    <span class="text-red-600 font-bold bg-red-100 px-1.5 py-0.5 rounded text-[11px]">
                                                        ⚠️ Stok Saat Ini: <span x-text="item.stock + ' ' + item.base_unit"></span> (Menipis)
                                                    </span>
                                                </template>
                                                <template x-if="Number(item.stock) > Number(item.minimum_stock)">
                                                    <span class="text-gray-500 text-[11px]">
                                                        Stok Saat Ini: <span class="font-semibold text-gray-700" x-text="item.stock + ' ' + item.base_unit"></span>
                                                    </span>
                                                </template>

                                                {{-- Batas Minimal Stok --}}
                                                <span class="text-gray-400">•</span>
                                                <span class="text-gray-600 text-[11px]">
                                                    Min. Stok: <strong class="text-gray-800" x-text="item.minimum_stock + ' ' + item.base_unit"></strong>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Tombol Hapus --}}
                                    <button
                                        type="button"
                                        @click="removeItem(index)"
                                        class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-1.5 rounded-lg text-xs font-semibold transition"
                                        title="Hapus dari pesanan"
                                    >
                                        🗑️ Hapus
                                    </button>
                                </div>

                                {{-- Kontrol Kuantitas Pesanan & Pintasan --}}
                                <div class="bg-gray-50/80 rounded-xl p-3 border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <label class="text-xs font-bold text-gray-700">
                                                Jumlah Pesanan:
                                            </label>
                                            <span class="text-xs text-gray-500">
                                                Satuan: <strong class="text-gray-800" x-text="item.base_unit"></strong>
                                            </span>
                                        </div>

                                        {{-- Tombol - / Input Angka / Tombol + --}}
                                        <div class="flex items-center gap-2">
                                            <button
                                                type="button"
                                                @click="adjustQuantity(item, -1)"
                                                :disabled="item.quantity <= 1"
                                                class="w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-700 font-bold hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
                                            >
                                                −
                                            </button>

                                            <input
                                                type="number"
                                                min="1"
                                                x-model.number="item.quantity"
                                                class="w-24 text-center font-bold px-2 py-1.5 border rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                :class="isInvalidQuantity(item) ? 'border-red-500 text-red-600 bg-red-50' : 'border-gray-300 text-gray-800'"
                                            >

                                            <button
                                                type="button"
                                                @click="adjustQuantity(item, 1)"
                                                class="w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-700 font-bold hover:bg-gray-100 transition"
                                            >
                                                +
                                            </button>

                                            {{-- Tombol Pintasan Cepat Set Minimal Stok --}}
                                            <button
                                                type="button"
                                                @click="setToMinStock(item)"
                                                class="text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 transition"
                                                title="Langsung sesuaikan ke batas minimal stok"
                                            >
                                                Min: <span x-text="item.minimum_stock"></span>
                                            </button>

                                            {{-- Pintasan +1 Package jika ada --}}
                                            <template x-if="item.items_per_package > 1">
                                                <button
                                                    type="button"
                                                    @click="adjustQuantity(item, item.items_per_package)"
                                                    class="text-xs font-semibold px-2 py-1.5 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition"
                                                    :title="'Tambah 1 Paket (' + item.items_per_package + ' ' + item.base_unit + ')'"
                                                >
                                                    +1 Pkg (<span x-text="item.items_per_package"></span>)
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- Subtotal Biaya Per Item --}}
                                    <div class="text-right sm:border-l sm:border-gray-200 sm:pl-4">
                                        <p class="text-[11px] text-gray-400">Estimasi Biaya</p>
                                        <p class="text-sm font-bold text-gray-800 mt-0.5" x-text="formatRupiah(item.quantity * item.purchase_price)"></p>
                                        <p class="text-[10px] text-gray-400">@ <span x-text="formatRupiah(item.purchase_price)"></span> / unit</p>
                                    </div>
                                </div>

                                {{-- Peringatan Validasi Merah Jika Jumlah Pesanan di Bawah Minimal Stok --}}
                                <template x-if="isInvalidQuantity(item)">
                                    <div class="mt-2.5 bg-red-100 border border-red-300 text-red-700 rounded-xl px-3 py-2 text-xs flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5">
                                            <span>⚠️</span>
                                            <span>
                                                Jumlah pesanan harus minimal <strong><span x-text="item.minimum_stock"></span> <span x-text="item.base_unit"></span></strong> (sesuai batas minimal stok produk).
                                            </span>
                                        </div>
                                        <button
                                            type="button"
                                            @click="setToMinStock(item)"
                                            class="font-bold underline text-red-800 hover:text-red-900 shrink-0"
                                        >
                                            Sesuaikan
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Ringkasan Total PO --}}
                        <div class="bg-gray-900 text-white rounded-2xl p-5 mt-6 shadow-sm">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pb-4 border-b border-gray-800">
                                <div>
                                    <p class="text-xs text-gray-400">Total Jenis Produk</p>
                                    <p class="text-lg font-bold text-white mt-0.5" x-text="items.length + ' item'"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Total Kuantitas Pesanan</p>
                                    <p class="text-lg font-bold text-white mt-0.5" x-text="totalOrderUnits + ' unit'"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Estimasi Total Biaya</p>
                                    <p class="text-xl font-extrabold text-emerald-400 mt-0.5" x-text="formatRupiah(totalEstimatedCost)"></p>
                                </div>
                            </div>

                            {{-- Info Validasi Sebelum Submit --}}
                            <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                                <div class="text-xs">
                                    <template x-if="hasInvalidItems">
                                        <p class="text-red-400 font-bold flex items-center gap-1">
                                            <span>⚠️</span> Ada produk dengan jumlah di bawah batas minimal stok!
                                        </p>
                                    </template>
                                    <template x-if="!hasInvalidItems && items.length > 0">
                                        <p class="text-green-400 font-semibold flex items-center gap-1">
                                            <span>✅</span> Seluruh pesanan telah memenuhi batas minimal stok.
                                        </p>
                                    </template>
                                </div>

                                <div class="flex items-center gap-3 w-full sm:w-auto">
                                    <a
                                        href="{{ route('purchase-orders.index') }}"
                                        class="flex-1 sm:flex-initial text-center bg-gray-800 hover:bg-gray-700 text-gray-300 px-5 py-2.5 rounded-xl text-xs font-semibold transition"
                                    >
                                        Batal
                                    </a>

                                    <button
                                        type="submit"
                                        :disabled="!canSubmit"
                                        :class="canSubmit
                                            ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-md'
                                            : 'bg-gray-700 text-gray-500 cursor-not-allowed opacity-60'"
                                        class="flex-1 sm:flex-initial text-center px-6 py-2.5 rounded-xl text-xs font-bold transition"
                                    >
                                        Simpan Purchase Order
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
function purchaseOrderApp(suppliers, products, prefillProduct, prefillSupplier) {
    return {
        suppliers: suppliers || [],
        products: products || [],
        selectedSupplierId: prefillSupplier ? String(prefillSupplier.kode_supplier) : '',
        orderDate: '{{ old("order_date", date("Y-m-d")) }}',

        productSearch: '',
        productFilter: 'all',

        items: [],

        init() {
            if (this.selectedSupplierId) {
                this.onSupplierChange();

                // Jika ada prefill produk (misal dari tombol Pesan Langsung di Laporan Stok)
                if (prefillProduct) {
                    const found = this.products.find(p => String(p.kode_produk) === String(prefillProduct.kode_produk));
                    if (found) {
                        this.addItem(found);
                    }
                }
            }
        },

        get currentSupplier() {
            return this.suppliers.find(s => String(s.kode_supplier) === String(this.selectedSupplierId));
        },

        get supplierProducts() {
            if (!this.currentSupplier) return [];
            return this.products.filter(p => String(p.kode_kategori) === String(this.currentSupplier.kode_kategori));
        },

        get lowStockSupplierProducts() {
            return this.supplierProducts.filter(p => Number(p.stock) <= Number(p.minimum_stock));
        },

        get filteredCatalog() {
            let list = this.supplierProducts;
            if (this.productFilter === 'low_stock') {
                list = list.filter(p => Number(p.stock) <= Number(p.minimum_stock));
            }
            if (this.productSearch.trim()) {
                const q = this.productSearch.toLowerCase();
                list = list.filter(p => p.name.toLowerCase().includes(q) || p.kode_produk.toLowerCase().includes(q));
            }
            return list;
        },

        isItemInOrder(kodeProduk) {
            return this.items.some(item => String(item.kode_produk) === String(kodeProduk));
        },

        onSupplierChange() {
            this.productSearch = '';
            this.productFilter = 'all';

            if (this.currentSupplier) {
                // Pertahankan hanya item yang sesuai dengan kategori supplier ini
                this.items = this.items.filter(item => String(item.kode_kategori) === String(this.currentSupplier.kode_kategori));
            } else {
                this.items = [];
            }
        },

        addItem(product) {
            if (this.isItemInOrder(product.kode_produk)) return;

            // Default jumlah pesanan minimal = minimal_stock (atau 1 jika minimal_stock 0)
            const minQty = Math.max(1, Number(product.minimum_stock || 1));

            this.items.push({
                kode_produk: product.kode_produk,
                name: product.name,
                kode_kategori: product.kode_kategori,
                base_unit: product.base_unit || 'Unit',
                stock: Number(product.stock || 0),
                minimum_stock: Number(product.minimum_stock || 0),
                purchase_price: Number(product.purchase_price || 0),
                items_per_package: Number(product.items_per_package || 1),
                quantity: minQty,
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        addAllLowStock() {
            this.lowStockSupplierProducts.forEach(product => {
                if (!this.isItemInOrder(product.kode_produk)) {
                    this.addItem(product);
                }
            });
        },

        adjustQuantity(item, delta) {
            const next = Number(item.quantity || 0) + delta;
            item.quantity = Math.max(1, next);
        },

        setToMinStock(item) {
            item.quantity = Math.max(1, Number(item.minimum_stock || 1));
        },

        isInvalidQuantity(item) {
            const minAllowed = Number(item.minimum_stock || 0) > 0 ? Number(item.minimum_stock) : 1;
            return Number(item.quantity || 0) < minAllowed;
        },

        get hasInvalidItems() {
            return this.items.some(item => this.isInvalidQuantity(item));
        },

        get canSubmit() {
            if (!this.selectedSupplierId) return false;
            if (this.items.length === 0) return false;
            if (this.hasInvalidItems) return false;
            return true;
        },

        get totalOrderUnits() {
            return this.items.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
        },

        get totalEstimatedCost() {
            return this.items.reduce((sum, item) => sum + (Number(item.quantity || 0) * Number(item.purchase_price || 0)), 0);
        },

        formatRupiah(number) {
            return 'Rp ' + Number(number || 0).toLocaleString('id-ID');
        },

        prepareSubmit(event) {
            if (!this.canSubmit) {
                event.preventDefault();
                if (this.items.length === 0) {
                    alert('Pilih minimal satu produk untuk dipesan.');
                } else if (this.hasInvalidItems) {
                    alert('Jumlah pesanan untuk beberapa produk masih di bawah batas minimal stok. Mohon periksa kembali.');
                }
                return;
            }

            const container = document.getElementById('hidden-inputs');
            container.innerHTML = '';

            this.items.forEach((item, index) => {
                const kodeInput = document.createElement('input');
                kodeInput.type = 'hidden';
                kodeInput.name = `products[${index}][kode_produk]`;
                kodeInput.value = item.kode_produk;
                container.appendChild(kodeInput);

                const qtyInput = document.createElement('input');
                qtyInput.type = 'hidden';
                qtyInput.name = `products[${index}][quantity]`;
                qtyInput.value = item.quantity;
                container.appendChild(qtyInput);
            });
        }
    };
}
</script>
@endpush