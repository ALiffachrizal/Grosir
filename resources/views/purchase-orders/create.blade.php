@extends('layouts.app')

@section('title', 'Buat Purchase Order')
@section('page-title', 'Buat Purchase Order')
@section('page-subtitle', 'Buat pesanan barang baru')

@section('content')

<div
    class="bg-white rounded-xl shadow p-5"
    x-data="purchaseOrder(@js($suppliers), @js($products))"
>
    <form
        action="{{ route('purchase-orders.store') }}"
        method="POST"
        @submit="prepareSubmit($event)"
    >
        @csrf

        {{-- ========================================================= --}}
        {{-- DATA UTAMA PURCHASE ORDER --}}
        {{-- ========================================================= --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">

            {{-- Supplier --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Supplier <span class="text-red-500">*</span>
                </label>

                <select
                    name="kode_supplier"
                    x-model="selectedSupplierId"
                    @change="filterProducts()"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm
                           bg-white focus:outline-none focus:ring-2
                           focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">-- Pilih Supplier --</option>

                    @foreach($suppliers as $supplier)
                        <option
                            value="{{ $supplier->kode_supplier }}"
                            {{ old('kode_supplier') === $supplier->kode_supplier ? 'selected' : '' }}
                        >
                            {{ $supplier->name }}
                            ({{ $supplier->category->name ?? '-' }})
                        </option>
                    @endforeach
                </select>

                @error('kode_supplier')
                    <p class="mt-1 text-xs text-red-500">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Tanggal Order --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Tanggal Order <span class="text-red-500">*</span>
                </label>

                <input
                    type="date"
                    name="order_date"
                    value="{{ old('order_date', date('Y-m-d')) }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm
                           bg-white focus:outline-none focus:ring-2
                           focus:ring-blue-500 focus:border-transparent"
                >

                @error('order_date')
                    <p class="mt-1 text-xs text-red-500">
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- INFORMASI SUPPLIER DAN PRODUK --}}
        {{-- ========================================================= --}}

        {{-- Supplier belum dipilih --}}
        <div
            x-show="!selectedSupplierId"
            x-cloak
            class="flex items-start gap-3 bg-yellow-50 border border-yellow-200
                   rounded-xl px-4 py-3 text-sm text-yellow-700 mb-5"
        >
            <span class="text-lg">⚠️</span>

            <div>
                <p class="font-semibold">
                    Supplier belum dipilih
                </p>

                <p class="text-xs mt-0.5">
                    Pilih supplier terlebih dahulu agar produk sesuai kategori
                    supplier dapat ditampilkan.
                </p>
            </div>
        </div>

        {{-- Produk tidak ditemukan --}}
        <div
            x-show="selectedSupplierId && filteredProducts.length === 0"
            x-cloak
            class="flex items-start gap-3 bg-red-50 border border-red-200
                   rounded-xl px-4 py-3 text-sm text-red-700 mb-5"
        >
            <span class="text-lg">⚠️</span>

            <div>
                <p class="font-semibold">
                    Produk kategori
                    <span x-text="selectedCategory"></span>
                    belum tersedia.
                </p>

                <p class="text-xs mt-0.5">
                    Tambahkan produk dengan kategori tersebut melalui menu

                    <a
                        href="{{ route('products.create') }}"
                        class="underline font-semibold"
                    >
                        Kelola Produk
                    </a>.
                </p>
            </div>
        </div>

        {{-- Produk ditemukan --}}
        <div
            x-show="selectedSupplierId && filteredProducts.length > 0"
            x-cloak
            class="flex items-center gap-3 bg-green-50 border border-green-200
                   rounded-xl px-4 py-3 text-sm text-green-700 mb-5"
        >
            <span class="text-lg">✅</span>

            <p>
                Menampilkan produk kategori

                <strong x-text="selectedCategory"></strong>

                —

                <strong x-text="filteredProducts.length"></strong>

                produk tersedia.
            </p>
        </div>

        {{-- ========================================================= --}}
        {{-- DAFTAR PRODUK --}}
        {{-- ========================================================= --}}
        <div
            x-show="selectedSupplierId && filteredProducts.length > 0"
            x-cloak
            class="mb-5"
        >
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center
                        justify-between gap-3 mb-4">

                <div>
                    <h3 class="text-lg font-semibold text-gray-800">
                        Daftar Produk
                    </h3>

                    <p class="text-xs text-gray-500 mt-0.5">
                        Pilih produk dan masukkan jumlah package, bundle,
                        atau satuan.
                    </p>
                </div>

                <button
                    type="button"
                    @click="addRow()"
                    class="inline-flex items-center justify-center gap-2
                           bg-green-600 hover:bg-green-700 text-white
                           px-4 py-2.5 rounded-xl text-sm font-semibold
                           transition shadow-sm"
                >
                    <span class="text-lg leading-none">+</span>
                    Tambah Produk
                </button>
            </div>

            {{-- Daftar kartu --}}
            <div class="space-y-4">

                <template
                    x-for="(row, index) in rows"
                    :key="row.row_id"
                >
                    <div
                        class="border border-gray-200 rounded-2xl
                               overflow-hidden bg-white shadow-sm"
                    >
                        {{-- Header kartu --}}
                        <div class="flex items-center justify-between
                                    bg-gray-50 border-b border-gray-200
                                    px-4 py-2.5">

                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-blue-100
                                           text-blue-700 flex items-center
                                           justify-center text-sm font-bold"
                                    x-text="index + 1"
                                ></div>

                                <div>
                                    <p class="text-sm font-semibold text-gray-800">
                                        Produk Pesanan
                                        <span x-text="index + 1"></span>
                                    </p>

                                    <p
                                        x-show="row.kode_produk"
                                        class="text-xs text-gray-500"
                                    >
                                        Stok tersedia:

                                        <strong
                                            x-text="row.current_stock + ' ' + row.base_unit"
                                        ></strong>
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="removeRow(index)"
                                x-show="rows.length > 1"
                                class="inline-flex items-center gap-1.5
                                       bg-red-50 hover:bg-red-100
                                       text-red-600 px-3 py-1.5
                                       rounded-lg text-xs font-medium transition"
                            >
                                🗑️ Hapus
                            </button>
                        </div>

                        {{-- Isi kartu --}}
                        <div class="p-4">

                            {{-- Pilih produk --}}
                            <div class="mb-4">
                                <label
                                    class="block text-sm font-semibold
                                           text-gray-700 mb-2"
                                >
                                    Pilih Produk
                                    <span class="text-red-500">*</span>
                                </label>

                                <select
                                    x-model="row.kode_produk"
                                    @change="onProductChange(index)"
                                    class="w-full px-4 py-2.5 border
                                           border-gray-300 rounded-xl text-sm
                                           bg-white focus:outline-none
                                           focus:ring-2 focus:ring-blue-500
                                           focus:border-transparent"
                                >
                                    <option value="">
                                        -- Pilih Produk --
                                    </option>

                                    <template
                                        x-for="product in availableProducts(index)"
                                        :key="product.kode_produk"
                                    >
                                        <option
                                            :value="product.kode_produk"
                                            x-text="
                                                product.name +
                                                ' — Stok: ' +
                                                product.stock +
                                                ' ' +
                                                product.base_unit
                                            "
                                        ></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Kontrol jumlah --}}
                            <div
                                x-show="row.kode_produk"
                                x-cloak
                                class="grid grid-cols-1 sm:grid-cols-2
                                       lg:grid-cols-4 gap-3"
                            >
                                {{-- Package --}}
                                <div class="bg-gray-50 border border-gray-200
                                            rounded-xl p-3">

                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-xs font-semibold
                                                  text-gray-600 uppercase">
                                            Package
                                        </p>

                                        <span class="text-xs text-gray-400">
                                            <span x-text="row.items_per_package"></span>
                                            <span x-text="row.base_unit"></span>
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between
                                                bg-white border border-gray-200
                                                rounded-lg p-1">

                                        <button
                                            type="button"
                                            @click="
                                                row.package = Math.max(
                                                    0,
                                                    Number(row.package || 0) - 1
                                                );

                                                calculateTotal(index);
                                            "
                                            :disabled="row.package <= 0"
                                            class="w-9 h-9 rounded-md bg-gray-100
                                                   hover:bg-red-50 text-gray-600
                                                   hover:text-red-600 font-bold
                                                   disabled:opacity-40
                                                   disabled:cursor-not-allowed
                                                   transition"
                                        >
                                            −
                                        </button>

                                        <span
                                            class="text-base font-bold text-gray-800"
                                            x-text="row.package"
                                        ></span>

                                        <button
                                            type="button"
                                            @click="
                                                row.package =
                                                    Number(row.package || 0) + 1;

                                                calculateTotal(index);
                                            "
                                            class="w-9 h-9 rounded-md bg-blue-600
                                                   hover:bg-blue-700 text-white
                                                   font-bold transition"
                                        >
                                            +
                                        </button>
                                    </div>

                                    <p class="text-[11px] text-gray-400
                                              text-center mt-2">
                                        1
                                        <span
                                            x-text="row.package_label || 'Package'"
                                        ></span>

                                        =

                                        <span x-text="row.items_per_package"></span>

                                        <span x-text="row.base_unit"></span>
                                    </p>
                                </div>

                                {{-- Bundle --}}
                                <div class="bg-gray-50 border border-gray-200
                                            rounded-xl p-3">

                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-xs font-semibold
                                                  text-gray-600 uppercase">
                                            Bundle
                                        </p>

                                        <span class="text-xs text-gray-400">
                                            <span x-text="row.items_per_bundle"></span>
                                            <span x-text="row.base_unit"></span>
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between
                                                bg-white border border-gray-200
                                                rounded-lg p-1">

                                        <button
                                            type="button"
                                            @click="
                                                row.bundle = Math.max(
                                                    0,
                                                    Number(row.bundle || 0) - 1
                                                );

                                                calculateTotal(index);
                                            "
                                            :disabled="
                                                row.bundle <= 0 ||
                                                row.items_per_bundle <= 1
                                            "
                                            class="w-9 h-9 rounded-md bg-gray-100
                                                   hover:bg-red-50 text-gray-600
                                                   hover:text-red-600 font-bold
                                                   disabled:opacity-40
                                                   disabled:cursor-not-allowed
                                                   transition"
                                        >
                                            −
                                        </button>

                                        <span
                                            class="text-base font-bold text-gray-800"
                                            x-text="row.bundle"
                                        ></span>

                                        <button
                                            type="button"
                                            @click="
                                                row.bundle =
                                                    Number(row.bundle || 0) + 1;

                                                calculateTotal(index);
                                            "
                                            :disabled="row.items_per_bundle <= 1"
                                            class="w-9 h-9 rounded-md bg-blue-600
                                                   hover:bg-blue-700 text-white
                                                   font-bold
                                                   disabled:bg-gray-200
                                                   disabled:text-gray-400
                                                   disabled:cursor-not-allowed
                                                   transition"
                                        >
                                            +
                                        </button>
                                    </div>

                                    <p class="text-[11px] text-gray-400
                                              text-center mt-2">

                                        <template x-if="row.items_per_bundle > 1">
                                            <span>
                                                1 Bundle =

                                                <span
                                                    x-text="row.items_per_bundle"
                                                ></span>

                                                <span
                                                    x-text="row.base_unit"
                                                ></span>
                                            </span>
                                        </template>

                                        <template x-if="row.items_per_bundle <= 1">
                                            <span>
                                                Tidak tersedia
                                            </span>
                                        </template>
                                    </p>
                                </div>

                                {{-- Satuan --}}
                                <div class="bg-gray-50 border border-gray-200
                                            rounded-xl p-3">

                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-xs font-semibold
                                                  text-gray-600 uppercase">
                                            Satuan
                                        </p>

                                        <span
                                            class="text-xs text-gray-400"
                                            x-text="row.base_unit"
                                        ></span>
                                    </div>

                                    <div class="flex items-center justify-between
                                                bg-white border border-gray-200
                                                rounded-lg p-1">

                                        <button
                                            type="button"
                                            @click="
                                                row.unit = Math.max(
                                                    0,
                                                    Number(row.unit || 0) - 1
                                                );

                                                calculateTotal(index);
                                            "
                                            :disabled="row.unit <= 0"
                                            class="w-9 h-9 rounded-md bg-gray-100
                                                   hover:bg-red-50 text-gray-600
                                                   hover:text-red-600 font-bold
                                                   disabled:opacity-40
                                                   disabled:cursor-not-allowed
                                                   transition"
                                        >
                                            −
                                        </button>

                                        <span
                                            class="text-base font-bold text-gray-800"
                                            x-text="row.unit"
                                        ></span>

                                        <button
                                            type="button"
                                            @click="
                                                row.unit =
                                                    Number(row.unit || 0) + 1;

                                                calculateTotal(index);
                                            "
                                            class="w-9 h-9 rounded-md bg-blue-600
                                                   hover:bg-blue-700 text-white
                                                   font-bold transition"
                                        >
                                            +
                                        </button>
                                    </div>

                                    <p class="text-[11px] text-gray-400
                                              text-center mt-2">
                                        Jumlah dalam

                                        <span x-text="row.base_unit"></span>
                                    </p>
                                </div>

                                {{-- Total --}}
                                <div class="bg-blue-600 rounded-xl p-3 text-white">

                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-xs font-semibold
                                                  uppercase text-blue-100">
                                            Total Unit
                                        </p>

                                        <span class="text-xs text-blue-200">
                                            Pesanan
                                        </span>
                                    </div>

                                    <div class="h-[43px] flex items-center
                                                justify-center">

                                        <span
                                            class="text-2xl font-bold"
                                            x-text="row.total"
                                        ></span>

                                        <span
                                            class="text-sm ml-1 text-blue-100"
                                            x-text="row.base_unit"
                                        ></span>
                                    </div>

                                    <p class="text-[11px] text-blue-100
                                              text-center mt-2">
                                        Total jumlah produk
                                    </p>
                                </div>
                            </div>

                            {{-- Rincian perhitungan --}}
                            <div
                                x-show="row.kode_produk && row.total > 0"
                                x-cloak
                                class="mt-3 text-xs text-gray-500 bg-gray-50
                                       border border-gray-200 rounded-lg
                                       px-3 py-2"
                            >
                                Perhitungan:

                                (<span x-text="row.package"></span>
                                ×
                                <span x-text="row.items_per_package"></span>)

                                +

                                (<span x-text="row.bundle"></span>
                                ×
                                <span x-text="row.items_per_bundle"></span>)

                                +

                                <span x-text="row.unit"></span>

                                =

                                <strong
                                    class="text-gray-800"
                                    x-text="row.total"
                                ></strong>

                                <span x-text="row.base_unit"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- ===================================================== --}}
            {{-- RINGKASAN TOTAL --}}
            {{-- ===================================================== --}}
            <div
                class="mt-4 flex flex-col sm:flex-row sm:items-center
                       justify-between gap-3 bg-gray-800 text-white
                       rounded-xl px-5 py-3.5"
            >
                <div>
                    <p class="font-semibold text-sm">
                        Ringkasan Purchase Order
                    </p>

                    <p class="text-xs text-gray-300 mt-0.5">
                        Total dari seluruh produk yang dipesan.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-300">
                        Total Semua Produk
                    </span>

                    <div
                        class="bg-white text-gray-900 rounded-lg
                               px-4 py-2 min-w-[110px] text-center"
                    >
                        <span
                            class="text-xl font-bold"
                            x-text="grandTotal"
                        ></span>

                        <span class="text-xs text-gray-500">
                            unit
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hidden input --}}
        <div id="hidden-inputs"></div>

        {{-- ========================================================= --}}
        {{-- VALIDATION ERROR --}}
        {{-- ========================================================= --}}
        @error('products')
            <div class="mb-4 bg-red-50 border border-red-200
                        rounded-xl px-4 py-3 text-sm text-red-600">
                {{ $message }}
            </div>
        @enderror

        @error('products.*.kode_produk')
            <div class="mb-4 bg-red-50 border border-red-200
                        rounded-xl px-4 py-3 text-sm text-red-600">
                {{ $message }}
            </div>
        @enderror

        @error('products.*.quantity')
            <div class="mb-4 bg-red-50 border border-red-200
                        rounded-xl px-4 py-3 text-sm text-red-600">
                {{ $message }}
            </div>
        @enderror

        {{-- ========================================================= --}}
        {{-- TOMBOL AKSI --}}
        {{-- ========================================================= --}}
        <div class="flex flex-col sm:flex-row gap-3">

            <button
                type="submit"
                :disabled="!canSubmit"
                :class="
                    canSubmit
                        ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm'
                        : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                "
                class="flex-1 py-3 rounded-xl text-sm
                       font-semibold transition"
            >
                Simpan Purchase Order
            </button>

            <a
                href="{{ route('purchase-orders.index') }}"
                class="flex-1 text-center bg-gray-100 hover:bg-gray-200
                       text-gray-700 py-3 rounded-xl text-sm
                       font-medium transition"
            >
                Batal
            </a>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
function purchaseOrder(suppliers, products) {
    return {
        suppliers: suppliers,
        products: products,

        selectedSupplierId: '',
        selectedCategory: '',
        filteredProducts: [],

        rowCounter: 1,

        rows: [
            {
                row_id: 1,
                kode_produk: '',
                package: 0,
                bundle: 0,
                unit: 0,
                total: 0,
                base_unit: '',
                package_label: '',
                items_per_package: 1,
                items_per_bundle: 1,
                current_stock: 0,
            }
        ],

        /*
        |--------------------------------------------------------------------------
        | Total seluruh produk
        |--------------------------------------------------------------------------
        */
        get grandTotal() {
            return this.rows.reduce((sum, row) => {
                return sum + Number(row.total || 0);
            }, 0);
        },

        /*
        |--------------------------------------------------------------------------
        | Form dapat disimpan
        |--------------------------------------------------------------------------
        */
        get canSubmit() {
            if (!this.selectedSupplierId) {
                return false;
            }

            if (this.filteredProducts.length === 0) {
                return false;
            }

            return this.rows.some(row => {
                return row.kode_produk && Number(row.total) > 0;
            });
        },

        /*
        |--------------------------------------------------------------------------
        | Mengambil nama kategori
        |--------------------------------------------------------------------------
        */
        getCategoryName(item) {
            if (!item) {
                return '';
            }

            if (item.category_name) {
                return item.category_name;
            }

            if (
                item.category &&
                typeof item.category === 'object'
            ) {
                return item.category.name || '';
            }

            if (
                item.category &&
                typeof item.category === 'string'
            ) {
                return item.category;
            }

            return '';
        },

        /*
        |--------------------------------------------------------------------------
        | Normalisasi nama kategori
        |--------------------------------------------------------------------------
        */
        normalize(value) {
            return (value || '')
                .toString()
                .trim()
                .toUpperCase();
        },

        /*
        |--------------------------------------------------------------------------
        | Membuat baris kosong
        |--------------------------------------------------------------------------
        */
        createEmptyRow() {
            this.rowCounter++;

            return {
                row_id: this.rowCounter,
                kode_produk: '',
                package: 0,
                bundle: 0,
                unit: 0,
                total: 0,
                base_unit: '',
                package_label: '',
                items_per_package: 1,
                items_per_bundle: 1,
                current_stock: 0,
            };
        },

        /*
        |--------------------------------------------------------------------------
        | Reset baris
        |--------------------------------------------------------------------------
        */
        resetRows() {
            this.rowCounter++;

            this.rows = [
                {
                    row_id: this.rowCounter,
                    kode_produk: '',
                    package: 0,
                    bundle: 0,
                    unit: 0,
                    total: 0,
                    base_unit: '',
                    package_label: '',
                    items_per_package: 1,
                    items_per_bundle: 1,
                    current_stock: 0,
                }
            ];
        },

        /*
        |--------------------------------------------------------------------------
        | Filter produk berdasarkan kategori supplier
        |--------------------------------------------------------------------------
        |
        | PENTING: produk dicocokkan ke supplier lewat category_id (relasi
        | database yang sebenarnya), BUKAN lewat nama kategori sebagai teks.
        |
        | Sebelumnya sistem membandingkan nama kategori produk dengan nama
        | kategori supplier sebagai string. Ini rawan bug: dua kategori
        | dengan nama yang mirip tapi tidak identik (typo, beda huruf
        | besar/kecil, atau spasi ekstra) tidak akan pernah "nyambung"
        | walau maksud admin sebenarnya sama.
        |
        | Sejak kategori produk & supplier digabung jadi satu tabel yang
        | sama (lihat migration merge_supplier_categories_into_product),
        | produk dan supplier yang satu kategori akan selalu punya
        | category_id yang SAMA PERSIS — jadi tidak mungkin salah connect
        | lagi, walau nama kategorinya diketik beda.
        */
        filterProducts() {
            if (!this.selectedSupplierId) {
                this.filteredProducts = [];
                this.selectedCategory = '';
                this.resetRows();

                return;
            }

            const supplier = this.suppliers.find(supplier => {
                return String(supplier.kode_supplier) ===
                    String(this.selectedSupplierId);
            });

            if (!supplier) {
                this.filteredProducts = [];
                this.selectedCategory = '';
                this.resetRows();

                return;
            }

            // Nama kategori tetap disimpan, tapi HANYA untuk ditampilkan
            // di UI (misalnya teks "Menampilkan produk kategori X").
            // Pencocokan produknya sendiri tidak lagi memakai nama ini.
            this.selectedCategory =
                this.getCategoryName(supplier);

            this.filteredProducts =
                this.products.filter(product => {
                    return Number(product.category_id) ===
                        Number(supplier.category_id);
                });

            this.resetRows();
        },

        /*
        |--------------------------------------------------------------------------
        | Menghindari produk dipilih dua kali
        |--------------------------------------------------------------------------
        */
        availableProducts(currentIndex) {
            const selectedCodes = this.rows
                .filter((row, index) => {
                    return index !== currentIndex &&
                        row.kode_produk;
                })
                .map(row => String(row.kode_produk));

            return this.filteredProducts.filter(product => {
                return !selectedCodes.includes(
                    String(product.kode_produk)
                );
            });
        },

        /*
        |--------------------------------------------------------------------------
        | Saat produk dipilih
        |--------------------------------------------------------------------------
        */
        onProductChange(index) {
            const row = this.rows[index];

            const product = this.products.find(product => {
                return String(product.kode_produk) ===
                    String(row.kode_produk);
            });

            row.package = 0;
            row.bundle = 0;
            row.unit = 0;
            row.total = 0;

            if (!product) {
                row.base_unit = '';
                row.package_label = '';
                row.items_per_package = 1;
                row.items_per_bundle = 1;
                row.current_stock = 0;

                return;
            }

            row.base_unit =
                product.base_unit || 'Unit';

            row.items_per_package =
                Number(product.items_per_package || 1);

            row.items_per_bundle =
                Number(product.items_per_bundle || 1);

            row.package_label =
                product.base_unit === 'KG'
                    ? 'Karung'
                    : 'Package';

            row.current_stock =
                Number(product.stock || 0);

            this.calculateTotal(index);
        },

        /*
        |--------------------------------------------------------------------------
        | Menghitung jumlah unit
        |--------------------------------------------------------------------------
        */
        calculateTotal(index) {
            const row = this.rows[index];

            row.package = Math.max(
                0,
                Number(row.package || 0)
            );

            row.bundle = Math.max(
                0,
                Number(row.bundle || 0)
            );

            row.unit = Math.max(
                0,
                Number(row.unit || 0)
            );

            const fromPackage =
                row.package *
                Number(row.items_per_package || 1);

            const fromBundle =
                row.bundle *
                Number(row.items_per_bundle || 1);

            const fromUnit =
                row.unit;

            row.total =
                fromPackage +
                fromBundle +
                fromUnit;
        },

        /*
        |--------------------------------------------------------------------------
        | Menambah baris
        |--------------------------------------------------------------------------
        */
        addRow() {
            if (
                this.rows.length >=
                this.filteredProducts.length
            ) {
                alert(
                    'Semua produk dalam kategori ini sudah dimasukkan.'
                );

                return;
            }

            this.rows.push(this.createEmptyRow());
        },

        /*
        |--------------------------------------------------------------------------
        | Menghapus baris
        |--------------------------------------------------------------------------
        */
        removeRow(index) {
            if (this.rows.length <= 1) {
                return;
            }

            this.rows.splice(index, 1);
        },

        /*
        |--------------------------------------------------------------------------
        | Menyiapkan data sebelum dikirim
        |--------------------------------------------------------------------------
        */
        prepareSubmit(event) {
            if (!this.canSubmit) {
                event.preventDefault();

                alert(
                    'Pilih minimal satu produk dan masukkan jumlah pesanan.'
                );

                return;
            }

            const container =
                document.getElementById('hidden-inputs');

            container.innerHTML = '';

            let validIndex = 0;

            this.rows.forEach(row => {
                if (
                    row.kode_produk &&
                    Number(row.total) > 0
                ) {
                    const kodeInput =
                        document.createElement('input');

                    kodeInput.type = 'hidden';

                    kodeInput.name =
                        `products[${validIndex}][kode_produk]`;

                    kodeInput.value =
                        row.kode_produk;

                    container.appendChild(kodeInput);

                    const quantityInput =
                        document.createElement('input');

                    quantityInput.type = 'hidden';

                    quantityInput.name =
                        `products[${validIndex}][quantity]`;

                    quantityInput.value =
                        Number(row.total);

                    container.appendChild(quantityInput);

                    validIndex++;
                }
            });
        }
    };
}
</script>
@endpush