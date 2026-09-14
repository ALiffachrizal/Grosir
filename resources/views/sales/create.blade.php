@extends('layouts.app')

@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale')

@section('content')

<div x-data="pos(@js($products), @js($categories), @js($initialCart), {{ $resumingDraftId ?? 'null' }})"
     class="flex flex-col">

    {{-- ===== PANEL UTAMA ===== --}}
    <div class="flex gap-4" style="height: calc(100vh - 120px)">

        {{-- ===== PANEL KIRI: PRODUK ===== --}}
        <div class="flex-1 bg-white rounded-2xl shadow-sm flex flex-col overflow-hidden">

            {{-- Search & Tombol Draft Tertunda --}}
            <div class="p-4 pb-2 flex items-center gap-3">
                <div class="relative flex-1">
                    <input type="text" x-model="search"
                           placeholder="🔍 Cari nama atau kode produk..."
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                    {{-- Indikator kecil saat pencarian sedang diproses di server --}}
                    <div x-show="isSearching"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         class="absolute right-4 top-1/2 -translate-y-1/2"
                         style="display:none">
                        <div class="w-4 h-4 border-2 border-blue-400 border-t-transparent rounded-full animate-spin"></div>
                    </div>
                </div>

                {{-- Tombol Draft Tertunda (Hanya tampil jika ada draft) --}}
                @if($drafts->count() > 0)
                    <button type="button"
                            @click="showDraftsModal = true"
                            class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold px-3.5 py-2.5 rounded-xl text-xs shadow-sm transition whitespace-nowrap">
                        <span>📝</span>
                        <span class="hidden sm:inline">Draft Tertunda</span>
                        <span class="bg-white text-amber-700 text-xs font-black px-1.5 py-0.5 rounded-full shadow-sm">
                            {{ $drafts->count() }}
                        </span>
                    </button>
                @endif
            </div>

            {{-- Tabs Kategori --}}
            <div class="px-4 pb-3">
                <div class="flex gap-2 overflow-x-auto pb-1">
                    <button @click="selectedCategory = ''"
                            :class="selectedCategory === ''
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-200'
                                : 'bg-white text-gray-500 border border-gray-200 hover:border-blue-300'"
                            class="px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all">
                        Semua
                    </button>

                    <template x-for="cat in categories" :key="cat.kode_kategori">
                        <button @click="selectedCategory = cat.name"
                                :class="selectedCategory === cat.name
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-200'
                                    : 'bg-white text-gray-500 border border-gray-200 hover:border-blue-300'"
                                class="px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all"
                                x-text="cat.name">
                        </button>
                    </template>
                </div>
            </div>

            {{-- Grid Produk --}}
            <div class="flex-1 overflow-y-auto px-4 pb-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
                    <template x-for="product in filteredProducts" :key="product.kode_produk">
                        <div @click="openModal(product)"
                             class="bg-white border border-gray-100 rounded-2xl p-4 cursor-pointer
                                    hover:border-blue-300 hover:shadow-lg hover:shadow-blue-50
                                    transition-all duration-200 group relative">

                            {{-- Badge Promo jika ada --}}
                            <div x-show="product.has_promo"
                                 class="absolute top-2 right-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm flex items-center gap-1">
                                <span>🎁</span>
                                <span x-text="product.promo_label"></span>
                            </div>

                            <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center
                                        mx-auto mb-3 group-hover:bg-blue-100 transition-colors">
                                <span class="text-2xl" x-text="getCategoryIcon(getCategoryName(product))"></span>
                            </div>

                            <p class="text-sm font-semibold text-gray-800 text-center leading-tight mb-1"
                               x-text="product.name"></p>

                            <p class="text-xs text-center mb-2"
                               :class="product.stock <= product.minimum_stock ? 'text-red-400' : 'text-gray-400'"
                               x-text="'Stok: ' + product.stock + ' ' + product.base_unit"></p>

                            <p class="text-sm font-bold text-blue-600 text-center"
                               x-text="'Rp ' + formatNumber(product.selling_price)"></p>
                        </div>
                    </template>

                    <div x-show="filteredProducts.length === 0"
                         class="col-span-full text-center py-16 text-gray-300">
                        <div class="text-5xl mb-3">📦</div>
                        <p class="text-sm">Produk tidak ditemukan</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== PANEL KANAN: KERANJANG ===== --}}
        <div class="w-80 xl:w-96 bg-white rounded-2xl shadow-sm flex flex-col">

            {{-- Header --}}
            <div class="p-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🛒</span>
                        <h3 class="font-bold text-gray-800 text-sm">Keranjang</h3>

                        <span x-show="cart.length > 0"
                              class="bg-blue-600 text-white text-xs font-bold w-5 h-5 rounded-full
                                     flex items-center justify-center"
                              x-text="cart.length"></span>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($drafts->count() > 0)
                            <button type="button"
                                    @click="showDraftsModal = true"
                                    class="text-xs bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-lg font-bold flex items-center gap-1 transition"
                                    title="Lihat transaksi tertunda">
                                <span>📝</span>
                                <span>{{ $drafts->count() }} Draft</span>
                            </button>
                        @endif

                        <button @click="clearCart()" x-show="cart.length > 0"
                                class="text-xs text-red-400 hover:text-red-600 transition">
                            🗑️ Kosongkan
                        </button>
                    </div>
                </div>

                @if($resumingDraftId)
                    <div class="mt-2 bg-blue-50 border border-blue-200 rounded-lg px-2.5 py-1.5 flex items-center justify-between text-xs text-blue-700">
                        <span class="truncate">📝 Melanjutkan Draft #{{ $resumingDraftId }}</span>
                        <a href="{{ route('sales.create') }}" class="text-[11px] font-bold underline text-blue-600 shrink-0 ml-1">
                            Mulai Baru
                        </a>
                    </div>
                @endif
            </div>

            {{-- List Keranjang --}}
            <div class="flex-1 overflow-y-auto p-4">

                <div x-show="cart.length === 0"
                     class="flex flex-col items-center justify-center h-full text-gray-300 py-8">
                    <div class="w-20 h-20 rounded-full bg-gray-50 flex items-center justify-center mb-4">
                        <span class="text-4xl">🛒</span>
                    </div>
                    <p class="text-sm font-medium text-gray-400">Belum ada barang</p>
                    <p class="text-xs text-gray-300 mt-1">Klik produk untuk menambahkan</p>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="bg-gray-50 rounded-xl p-3">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 truncate"
                                       x-text="item.name"></p>

                                    <p class="text-xs text-gray-400 mt-0.5"
                                       x-text="item.description"></p>

                                    <div x-show="calculateBonus(item.kode_produk, item.quantity) > 0"
                                         class="inline-flex items-center gap-1 bg-amber-100 text-amber-800 font-bold text-[11px] px-2 py-0.5 rounded-md mt-1">
                                        <span>🎁 Bonus: +<span x-text="calculateBonus(item.kode_produk, item.quantity)"></span> <span x-text="item.base_unit || ''"></span> (Gratis)</span>
                                    </div>
                                </div>

                                <button @click="removeFromCart(index)"
                                        class="w-5 h-5 rounded-full bg-red-100 hover:bg-red-200
                                               text-red-500 flex items-center justify-center
                                               text-xs transition shrink-0">
                                    ✕
                                </button>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <button @click="decreaseQty(index)"
                                            class="w-7 h-7 rounded-lg bg-white border border-gray-200
                                                   hover:border-red-300 hover:bg-red-50 text-gray-600
                                                   text-sm font-bold flex items-center justify-center transition">
                                        −
                                    </button>

                                    <span class="text-sm font-bold text-gray-800 w-6 text-center"
                                          x-text="item.quantity"></span>

                                    <button @click="increaseQty(index)"
                                            class="w-7 h-7 rounded-lg bg-blue-600 hover:bg-blue-700
                                                   text-white text-sm font-bold
                                                   flex items-center justify-center transition">
                                        +
                                    </button>
                                </div>

                                <p class="text-sm font-bold text-gray-800"
                                   x-text="'Rp ' + formatNumber(item.quantity * item.unit_price)"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Checkout Area --}}
            <div class="p-4 border-t border-gray-100 space-y-3">
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Subtotal</span>
                    <span x-text="'Rp ' + formatNumber(totalPrice)"></span>
                </div>

                <div class="flex justify-between items-center">
                    <span class="font-bold text-gray-800">TOTAL</span>
                    <span class="text-xl font-bold text-gray-900"
                          x-text="'Rp ' + formatNumber(totalPrice)"></span>
                </div>

                <div class="relative">
                    <select x-model="paymentMethod"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl
                                   text-sm font-medium text-gray-700 focus:outline-none
                                   focus:ring-2 focus:ring-blue-500 appearance-none cursor-pointer">
                        <option value="">Pilih Pembayaran</option>
                        <option value="cash">💵 Tunai</option>
                        <option value="transfer">🏦 Transfer</option>
                    </select>

                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none
                                text-gray-400 text-xs">
                        ▼
                    </div>
                </div>

                {{--
                    Dua tombol: "Simpan / Draft" TIDAK butuh metode pembayaran
                    (karena belum benar-benar dibayar), sedangkan "Bayar Sekarang"
                    tetap wajib pilih metode pembayaran dulu seperti biasa.
                --}}
                <div class="flex gap-2">
                    <button @click="saveDraft()"
                            :disabled="cart.length === 0"
                            :class="cart.length === 0
                                ? 'bg-gray-100 text-gray-300 cursor-not-allowed'
                                : 'bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 cursor-pointer'"
                            class="flex-1 py-3.5 rounded-xl text-xs font-bold transition-all duration-200">
                        💾 Simpan / Draft
                    </button>

                    <button @click="checkout()"
                            :disabled="cart.length === 0 || !paymentMethod"
                            :class="cart.length === 0 || !paymentMethod
                                ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                : 'bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-200 cursor-pointer'"
                            class="flex-[2] py-3.5 rounded-xl text-sm font-bold transition-all duration-200">
                        Bayar Sekarang
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- ===== MODAL PILIH SATUAN ===== --}}
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
         @click.self="showModal = false"
         style="display:none">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm"
             x-show="showModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">

            {{-- Modal Header --}}
            <div class="p-5 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                            <span x-text="getCategoryIcon(getCategoryName(selectedProduct))"></span>
                        </div>

                        <div>
                            <h3 class="font-bold text-gray-800 text-sm"
                                x-text="selectedProduct?.name"></h3>

                            <p class="text-xs text-gray-400"
                               x-text="getCategoryName(selectedProduct)"></p>
                        </div>
                    </div>

                    <button @click="showModal = false"
                            class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200
                                   flex items-center justify-center text-gray-500 transition">
                        ✕
                    </button>
                </div>
            </div>

            <div class="p-5 space-y-5">

                {{-- Pilih Satuan --}}
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                        Pilih Satuan
                    </p>

                    <div class="grid grid-cols-3 gap-2">

                        <button @click="selectUnit('base')"
                                :class="selectedUnit === 'base'
                                    ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-200'
                                    : 'bg-white text-gray-700 border-gray-200 hover:border-blue-300'"
                                class="border-2 rounded-xl py-3 text-xs font-semibold transition-all text-center">
                            <div class="text-lg mb-1">📦</div>
                            <div x-text="selectedProduct?.base_unit"></div>
                            <div class="text-xs opacity-60 mt-0.5">Satuan</div>
                        </button>

                        <button @click="selectUnit('package')"
                                :class="selectedUnit === 'package'
                                    ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-200'
                                    : 'bg-white text-gray-700 border-gray-200 hover:border-blue-300'"
                                class="border-2 rounded-xl py-3 text-xs font-semibold transition-all text-center">
                            <div class="text-lg mb-1">📫</div>
                            <div x-text="selectedProduct?.base_unit === 'KG' ? 'Karung' : 'Package'"></div>
                            <div class="text-xs opacity-60 mt-0.5"
                                 x-text="selectedProduct?.items_per_package + ' ' + selectedProduct?.base_unit">
                            </div>
                        </button>

                        <button @click="selectUnit('bundle')"
                                x-show="selectedProduct?.items_per_bundle > 1"
                                :class="selectedUnit === 'bundle'
                                    ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-200'
                                    : 'bg-white text-gray-700 border-gray-200 hover:border-blue-300'"
                                class="border-2 rounded-xl py-3 text-xs font-semibold transition-all text-center">
                            <div class="text-lg mb-1">🎁</div>
                            <div>Bundle</div>
                            <div class="text-xs opacity-60 mt-0.5"
                                 x-text="selectedProduct?.items_per_bundle + ' ' + selectedProduct?.base_unit">
                            </div>
                        </button>

                    </div>
                </div>

                {{-- Input Jumlah --}}
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                        Jumlah
                    </p>

                    <div class="flex items-center justify-between bg-gray-50 rounded-xl p-3">
                        <button type="button" @click="modalQty > 1 ? modalQty-- : null"
                                class="w-10 h-10 rounded-xl bg-white border border-gray-200
                                       hover:border-red-300 hover:bg-red-50 text-gray-700 font-bold text-xl
                                       flex items-center justify-center shadow-sm transition select-none">
                            −
                        </button>

                        <span class="text-2xl font-bold text-gray-800 w-16 text-center"
                              x-text="modalQty"></span>

                        <button type="button" @click="modalQty++"
                                class="w-10 h-10 rounded-xl bg-blue-600 hover:bg-blue-700
                                       text-white font-bold text-xl
                                       flex items-center justify-center shadow-sm transition select-none">
                            +
                        </button>
                    </div>
                </div>

                {{-- Preview Harga --}}
                <div class="bg-blue-50 rounded-xl p-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Unit Beli</span>
                        <span class="font-semibold text-gray-800"
                              x-text="totalUnits + ' ' + (selectedProduct?.base_unit || '')"></span>
                    </div>

                    {{-- Bonus Promo Gratis --}}
                    <div x-show="modalBonus > 0"
                         class="flex justify-between text-sm bg-amber-100 text-amber-900 px-3 py-2 rounded-lg font-semibold border border-amber-200">
                        <span class="flex items-center gap-1.5">
                            <span>🎁</span>
                            <span>Bonus Promo Gratis</span>
                        </span>
                        <span class="font-bold text-amber-900"
                              x-text="'+ ' + modalBonus + ' ' + (selectedProduct?.base_unit || '')"></span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Harga / Unit</span>
                        <span class="font-semibold text-gray-800"
                              x-text="'Rp ' + formatNumber(unitPrice)"></span>
                    </div>

                    <div class="flex justify-between text-sm border-t border-blue-100 pt-2">
                        <span class="font-bold text-gray-700">Subtotal</span>
                        <span class="font-bold text-blue-600 text-base"
                              x-text="'Rp ' + formatNumber(totalUnits * unitPrice)"></span>
                    </div>
                </div>

                {{-- Warning Stok --}}
                <div x-show="(totalUnits + modalBonus) > (selectedProduct?.stock || 0)"
                     class="flex items-center gap-2 bg-red-50 border border-red-100 rounded-xl px-4 py-3">
                    <span>⚠️</span>
                    <p class="text-red-600 text-xs font-medium">
                        Stok tidak cukup! Dibutuhkan:
                        <strong x-text="(totalUnits + modalBonus) + ' ' + (selectedProduct?.base_unit || '')"></strong>
                        <span x-show="modalBonus > 0" x-text="' (termasuk ' + modalBonus + ' bonus gratis)'"></span>,
                        tersedia:
                        <span x-text="(selectedProduct?.stock || 0) + ' ' + (selectedProduct?.base_unit || '')"></span>
                    </p>
                </div>

                {{-- Tombol Tambah --}}
                <button @click="addToCart()"
                        :disabled="(totalUnits + modalBonus) > (selectedProduct?.stock || 0) || totalUnits === 0"
                        :class="(totalUnits + modalBonus) > (selectedProduct?.stock || 0) || totalUnits === 0
                            ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                            : 'bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-200'"
                        class="w-full py-3.5 rounded-xl text-sm font-bold transition-all">
                    + Tambah ke Keranjang
                </button>

            </div>
        </div>
    </div>

    {{-- ===== MODAL: HAPUS DRAFT ===== --}}
    <div x-show="showDeleteDraftModal"
         x-cloak
         @keydown.escape.window="showDeleteDraftModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        <div x-show="showDeleteDraftModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showDeleteDraftModal = false"
             class="absolute inset-0 bg-gray-900/50"></div>

        <div x-show="showDeleteDraftModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6">

            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center text-3xl mb-4">
                    🗑️
                </div>

                <h3 class="text-lg font-bold text-gray-800">
                    Hapus Draft?
                </h3>

                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                    Yakin ingin menghapus
                    <span class="font-semibold text-gray-700" x-text="deleteDraftLabel"></span>?
                    Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="flex gap-3 w-full mt-6">
                    <button type="button"
                            @click="showDeleteDraftModal = false"
                            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700
                                   py-2.5 rounded-xl text-sm font-semibold transition">
                        Batal
                    </button>

                    <button type="button"
                            @click="$refs.deleteDraftForm.action = deleteDraftAction; $refs.deleteDraftForm.submit()"
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white
                                   py-2.5 rounded-xl text-sm font-semibold shadow-sm transition">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL: DAFTAR TRANSAKSI TERTUNDA (DRAFT) ===== --}}
    <div x-show="showDraftsModal"
         x-cloak
         @keydown.escape.window="showDraftsModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        {{-- Backdrop --}}
        <div x-show="showDraftsModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showDraftsModal = false"
             class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

        {{-- Modal Dialog --}}
        <div x-show="showDraftsModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[85vh] flex flex-col overflow-hidden">

            {{-- Header --}}
            <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-amber-50/60">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl font-bold">
                        📝
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-base">
                            Transaksi Tertunda ({{ $drafts->count() }})
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Pilih transaksi yang ingin dilanjutkan ke kasir
                        </p>
                    </div>
                </div>

                <button type="button"
                        @click="showDraftsModal = false"
                        class="w-8 h-8 rounded-full bg-white hover:bg-gray-100 text-gray-500 flex items-center justify-center transition border border-gray-200">
                    ✕
                </button>
            </div>

            {{-- Body: Daftar Draft Card --}}
            <div class="p-5 overflow-y-auto flex-1 space-y-3">
                @forelse($drafts as $draft)
                    @php
                        $draftTotal = $draft->details->sum(function($d) {
                            return $d->quantity * ($d->unit_price ?? 0);
                        });
                    @endphp
                    <div class="border border-gray-200 hover:border-amber-400 rounded-2xl p-4 transition bg-white hover:shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="bg-amber-100 text-amber-800 text-xs font-bold px-2 py-0.5 rounded-md">
                                    {{ $draft->note ?: 'Draft #' . $draft->id }}
                                </span>
                                <span class="text-[11px] text-gray-400">
                                    {{ $draft->user->username }} · {{ $draft->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <p class="text-xs text-gray-600 mt-1">
                                <strong>{{ $draft->details->count() }} jenis produk</strong> · {{ $draft->details->sum('quantity') }} unit barang
                            </p>

                            {{-- Preview isi barang --}}
                            <div class="mt-2 text-[11px] text-gray-600 bg-gray-50 rounded-xl p-2.5 max-h-24 overflow-y-auto space-y-1">
                                @foreach($draft->details->take(3) as $d)
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="truncate font-medium">{{ $d->product->name ?? $d->kode_produk }}</span>
                                        <span class="font-bold text-gray-700 shrink-0">
                                            {{ $d->quantity }} {{ $d->product->base_unit ?? '' }}
                                            @if($d->bonus_quantity > 0)
                                                <span class="text-amber-600 font-extrabold text-[10px]">(+{{ $d->bonus_quantity }} Bonus)</span>
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                                @if($draft->details->count() > 3)
                                    <p class="text-[10px] text-blue-600 italic mt-0.5">+ {{ $draft->details->count() - 3 }} produk lainnya</p>
                                @endif
                            </div>

                            @if($draftTotal > 0)
                                <p class="text-xs font-bold text-gray-800 mt-2">
                                    Estimasi Nilai: <span class="text-emerald-600 font-extrabold">Rp {{ number_format($draftTotal, 0, ',', '.') }}</span>
                                </p>
                            @endif
                        </div>

                        {{-- Tombol Lanjutkan & Hapus --}}
                        <div class="flex sm:flex-col items-center gap-2 shrink-0">
                            <a href="{{ route('sales.create.resume', $draft) }}"
                               class="w-full text-center bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-4 py-2 rounded-xl transition shadow-sm inline-flex items-center justify-center gap-1.5 whitespace-nowrap">
                                <span>Lanjutkan</span>
                                <span>→</span>
                            </a>

                            <button type="button"
                                    @click="showDraftsModal = false; confirmDeleteDraft('{{ route('sales.draft.destroy', $draft) }}', '{{ addslashes($draft->note ?: 'Draft #'.$draft->id) }}')"
                                    class="w-full text-center bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold px-4 py-1.5 rounded-xl transition inline-flex items-center justify-center gap-1 whitespace-nowrap">
                                <span>🗑️ Hapus</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-gray-400 text-sm">
                        <span class="text-3xl block mb-2">📝</span>
                        <p class="font-medium">Tidak ada transaksi tertunda saat ini.</p>
                    </div>
                @endforelse
            </div>

            {{-- Footer --}}
            <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end">
                <button type="button"
                        @click="showDraftsModal = false"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-xl text-xs font-bold transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- Form hapus draft (hidden, action-nya diisi dinamis) --}}
    <form x-ref="deleteDraftForm" method="POST" style="display:none">
        @csrf
        @method('DELETE')
    </form>

    {{-- Form Submit Hidden — Bayar --}}
    <form id="checkout-form" action="{{ route('sales.store') }}" method="POST" style="display:none">
        @csrf
        <div id="checkout-inputs"></div>
    </form>

    {{-- Form Submit Hidden — Simpan/Draft --}}
    <form id="draft-form" action="{{ route('sales.draft.store') }}" method="POST" style="display:none">
        @csrf
        <div id="draft-inputs"></div>
    </form>

</div>

@endsection

@push('scripts')
<script>
function pos(products, categories, initialCart, resumingDraftId) {
    return {
        products: products,
        categories: categories,
        search: '',
        selectedCategory: '',
        cart: initialCart || [],
        paymentMethod: '',
        showModal: false,
        showDraftsModal: false,
        selectedProduct: null,
        selectedUnit: 'base',
        modalQty: 1,

        resumingDraftId: resumingDraftId || null,

        showDeleteDraftModal: false,
        deleteDraftAction: '',
        deleteDraftLabel: '',

        isSearching: false,
        _debounceTimer: null,
        knownProducts: {},

        init() {
            this.cacheProducts(this.products);
            this.$watch('search', () => this.debouncedFetch());
            this.$watch('selectedCategory', () => this.debouncedFetch());
        },

        cacheProducts(list) {
            list.forEach(p => {
                this.knownProducts[p.kode_produk] = p;
            });
        },

        debouncedFetch() {
            clearTimeout(this._debounceTimer);
            this._debounceTimer = setTimeout(() => {
                this.fetchProducts();
            }, 300);
        },

        async fetchProducts() {
            this.isSearching = true;

            const params = new URLSearchParams();
            if (this.search) params.set('q', this.search);
            if (this.selectedCategory) params.set('category', this.selectedCategory);

            try {
                const response = await fetch(
                    `{{ route('sales.products.search') }}?${params.toString()}`,
                    { headers: { 'Accept': 'application/json' } }
                );

                if (!response.ok) {
                    throw new Error('Gagal memuat produk');
                }

                const results = await response.json();

                this.products = results;
                this.cacheProducts(results);
            } catch (error) {
                console.error(error);
                
            } finally {
                this.isSearching = false;
            }
        },

        
        get filteredProducts() {
            return this.products;
        },

        get totalPrice() {
            return this.cart.reduce((sum, item) => {
                return sum + (item.quantity * item.unit_price);
            }, 0);
        },

        get totalUnits() {
            if (!this.selectedProduct) {
                return 0;
            }

            if (this.selectedUnit === 'base') {
                return this.modalQty;
            }

            if (this.selectedUnit === 'package') {
                return this.modalQty * this.selectedProduct.items_per_package;
            }

            if (this.selectedUnit === 'bundle') {
                return this.modalQty * this.selectedProduct.items_per_bundle;
            }

            return 0;
        },

        get modalBonus() {
            if (!this.selectedProduct) return 0;
            const minQty = Number(this.selectedProduct.promo_min_qty) || 0;
            const bonusQty = Number(this.selectedProduct.promo_bonus_qty) || 0;
            if (minQty <= 0 || bonusQty <= 0) return 0;
            return Math.floor(this.totalUnits / minQty) * bonusQty;
        },

        calculateBonus(kode_produk, qty) {
            const product = this.knownProducts[kode_produk];
            if (!product) return 0;
            const minQty = Number(product.promo_min_qty) || 0;
            const bonusQty = Number(product.promo_bonus_qty) || 0;
            if (minQty <= 0 || bonusQty <= 0) return 0;
            return Math.floor((Number(qty) || 0) / minQty) * bonusQty;
        },

        get unitPrice() {
            if (!this.selectedProduct) {
                return 0;
            }

            return this.selectedProduct.selling_price;
        },

        formatNumber(n) {
            return new Intl.NumberFormat('id-ID').format(n || 0);
        },

        getCategoryName(product) {
            if (!product) {
                return '-';
            }

            if (product.category && typeof product.category === 'object') {
                return product.category.name ?? '-';
            }

            if (product.category && typeof product.category === 'string') {
                return product.category;
            }

            if (product.category_name) {
                return product.category_name;
            }

            return '-';
        },

        getCategoryIcon(category) {
            const icons = {
                'SEMBAKO': '🌾',
                'Sembako': '🌾',

                'JAJANAN / SNACK': '🍿',
                'Jajanan / Snack': '🍿',

                'KEBUTUHAN RUMAH TANGGA': '🧴',
                'Kebutuhan Rumah Tangga': '🧴',

                'MINUMAN': '🥤',
                'Minuman': '🥤',

                'BUMBU DAPUR': '🧂',
                'Bumbu Dapur': '🧂',

                'PERAWATAN TUBUH': '🧼',
                'Perawatan Tubuh': '🧼',

                'BANGUNAN': '🧱',
                'Bangunan': '🧱',
            };

            return icons[category] || '📦';
        },

        openModal(product) {
            this.selectedProduct = product;
            this.selectedUnit = 'base';
            this.modalQty = 1;
            this.showModal = true;
        },

        selectUnit(unit) {
            this.selectedUnit = unit;
            this.modalQty = 1;
        },

        addToCart() {
            if (this.totalUnits <= 0) {
                return;
            }

            const product = this.selectedProduct;
            const bonusUnits = this.modalBonus;
            const neededUnits = this.totalUnits + bonusUnits;

            // Hitung unit + bonus yang sudah ada di cart untuk produk ini
            let existingUnitsInCart = 0;
            this.cart.forEach(item => {
                if (item.kode_produk === product.kode_produk) {
                    existingUnitsInCart += item.quantity + this.calculateBonus(item.kode_produk, item.quantity);
                }
            });

            if (existingUnitsInCart + neededUnits > product.stock) {
                alert('Stok tidak mencukupi! Tersedia ' + product.stock + ' ' + product.base_unit + ', dibutuhkan total ' + (existingUnitsInCart + neededUnits) + ' ' + product.base_unit + ' (termasuk bonus gratis).');
                return;
            }

            let description = '';

            if (this.selectedUnit === 'base') {
                description = this.modalQty + ' ' + product.base_unit;
            } else if (this.selectedUnit === 'package') {
                const label = product.base_unit === 'KG' ? 'Karung' : 'Package';
                description = this.modalQty + ' ' + label + ' (' + this.totalUnits + ' ' + product.base_unit + ')';
            } else if (this.selectedUnit === 'bundle') {
                description = this.modalQty + ' Bundle (' + this.totalUnits + ' ' + product.base_unit + ')';
            }

            const existing = this.cart.findIndex(
                i => i.kode_produk === product.kode_produk && i.description === description
            );

            if (existing >= 0) {
                this.cart[existing].quantity += this.totalUnits;
                this.cart[existing].bonus_quantity = this.calculateBonus(product.kode_produk, this.cart[existing].quantity);
            } else {
                this.cart.push({
                    kode_produk: product.kode_produk,
                    name: product.name,
                    base_unit: product.base_unit,
                    quantity: this.totalUnits,
                    bonus_quantity: bonusUnits,
                    unit_price: product.selling_price,
                    description: description,
                });
            }

            this.showModal = false;
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        decreaseQty(index) {
            if (this.cart[index].quantity > 1) {
                this.cart[index].quantity--;
                this.cart[index].bonus_quantity = this.calculateBonus(this.cart[index].kode_produk, this.cart[index].quantity);
            } else {
                this.removeFromCart(index);
            }
        },

        increaseQty(index) {
            const item = this.cart[index];
            const product = this.knownProducts[item.kode_produk];

            if (!product) {
                alert('Data produk tidak ditemukan. Coba muat ulang halaman.');
                return;
            }

            const newQty = item.quantity + 1;
            const newBonus = this.calculateBonus(item.kode_produk, newQty);

            // Hitung pemakaian stok produk ini oleh item lain di cart
            const otherItemsTotal = this.cart
                .filter((i, idx) => i.kode_produk === item.kode_produk && idx !== index)
                .reduce((sum, i) => sum + i.quantity + this.calculateBonus(i.kode_produk, i.quantity), 0);

            if (otherItemsTotal + newQty + newBonus <= product.stock) {
                item.quantity = newQty;
                item.bonus_quantity = newBonus;
            } else {
                alert('Stok tidak mencukupi untuk memenuhi pembelian dan bonus gratis!');
            }
        },

        clearCart() {
            if (confirm('Kosongkan keranjang?')) {
                this.cart = [];
                this.paymentMethod = '';
                this.resumingDraftId = null;
            }
        },

        confirmDeleteDraft(action, label) {
            this.deleteDraftAction = action;
            this.deleteDraftLabel = label;
            this.showDeleteDraftModal = true;
        },

        checkout() {
            if (this.cart.length === 0) {
                return;
            }

            if (!this.paymentMethod) {
                alert('Pilih metode pembayaran terlebih dahulu!');
                return;
            }

            const container = document.getElementById('checkout-inputs');
            container.innerHTML = '';

            const pmInput = document.createElement('input');
            pmInput.type = 'hidden';
            pmInput.name = 'payment_method';
            pmInput.value = this.paymentMethod;
            container.appendChild(pmInput);

            if (this.resumingDraftId) {
                const draftInput = document.createElement('input');
                draftInput.type = 'hidden';
                draftInput.name = 'draft_sale_id';
                draftInput.value = this.resumingDraftId;
                container.appendChild(draftInput);
            }

            this.cart.forEach((item, index) => {
                const bonus = this.calculateBonus(item.kode_produk, item.quantity);
                const fields = {
                    [`items[${index}][kode_produk]`]: item.kode_produk,
                    [`items[${index}][quantity]`]: item.quantity,
                    [`items[${index}][bonus_quantity]`]: bonus,
                    [`items[${index}][unit_price]`]: item.unit_price,
                    [`items[${index}][description]`]: item.description,
                };

                Object.entries(fields).forEach(([name, value]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    container.appendChild(input);
                });
            });

            document.getElementById('checkout-form').submit();
        },

        saveDraft() {
            if (this.cart.length === 0) {
                return;
            }

            const container = document.getElementById('draft-inputs');
            container.innerHTML = '';

            if (this.resumingDraftId) {
                const draftIdInput = document.createElement('input');
                draftIdInput.type = 'hidden';
                draftIdInput.name = 'draft_sale_id';
                draftIdInput.value = this.resumingDraftId;
                container.appendChild(draftIdInput);
            }

            this.cart.forEach((item, index) => {
                const bonus = this.calculateBonus(item.kode_produk, item.quantity);
                const fields = {
                    [`items[${index}][kode_produk]`]: item.kode_produk,
                    [`items[${index}][quantity]`]: item.quantity,
                    [`items[${index}][bonus_quantity]`]: bonus,
                };

                Object.entries(fields).forEach(([name, value]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    container.appendChild(input);
                });
            });

            document.getElementById('draft-form').submit();
        }
    }
}
</script>
@endpush