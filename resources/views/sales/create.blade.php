@extends('layouts.app')

@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale')

@section('content')

<div x-data="pos({{ $products->toJson() }}, {{ $categories->toJson() }})"
     class="flex flex-col gap-4">

    {{-- ===== PANEL UTAMA ===== --}}
    <div class="flex gap-4" style="height: calc(100vh - 140px)">

        {{-- ===== PANEL KIRI: PRODUK ===== --}}
        <div class="flex-1 bg-white rounded-2xl shadow-sm flex flex-col overflow-hidden">

            {{-- Search --}}
            <div class="p-4">
                <input type="text" x-model="search"
                       placeholder="Cari produk..."
                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                    <template x-for="cat in categories" :key="cat.id">
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
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="openModal(product)"
                             class="bg-white border border-gray-100 rounded-2xl p-4 cursor-pointer
                                    hover:border-blue-300 hover:shadow-lg hover:shadow-blue-50
                                    transition-all duration-200 group">
                            <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center mx-auto mb-3
                                        group-hover:bg-blue-100 transition-colors">
                                <span class="text-2xl" x-text="getCategoryIcon(product.category)"></span>
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
            <div class="p-5 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🛒</span>
                        <h3 class="font-bold text-gray-800">Keranjang</h3>
                        <span x-show="cart.length > 0"
                              class="bg-blue-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center"
                              x-text="cart.length"></span>
                    </div>
                    <button @click="clearCart()" x-show="cart.length > 0"
                            class="text-xs text-red-400 hover:text-red-600 transition">
                        🗑️ Kosongkan
                    </button>
                </div>
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
                                    <p class="text-sm font-semibold text-gray-800 truncate" x-text="item.name"></p>
                                    <p class="text-xs text-gray-400 mt-0.5" x-text="item.description"></p>
                                </div>
                                <button @click="removeFromCart(index)"
                                        class="w-5 h-5 rounded-full bg-red-100 hover:bg-red-200 text-red-500
                                               flex items-center justify-center text-xs transition shrink-0">
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
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm
                                   font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500
                                   appearance-none cursor-pointer">
                        <option value="">Pilih Pembayaran</option>
                        <option value="cash">💵 Tunai</option>
                        <option value="transfer">🏦 Transfer</option>
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400 text-xs">
                        ▼
                    </div>
                </div>

                <button @click="checkout()"
                        :disabled="cart.length === 0 || !paymentMethod"
                        :class="cart.length === 0 || !paymentMethod
                            ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                            : 'bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-200 cursor-pointer'"
                        class="w-full py-3.5 rounded-xl text-sm font-bold transition-all duration-200">
                    Bayar Sekarang
                </button>
            </div>

        </div>
        {{-- Akhir Panel Kanan --}}

    </div>
    {{-- Akhir Panel Utama --}}

    {{-- ===== MODAL PILIH SATUAN (di dalam x-data) ===== --}}
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
                            <span x-text="getCategoryIcon(selectedProduct?.category)"></span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm" x-text="selectedProduct?.name"></h3>
                            <p class="text-xs text-gray-400" x-text="selectedProduct?.category"></p>
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
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Pilih Satuan</p>
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
                                 x-text="selectedProduct?.items_per_package + ' ' + selectedProduct?.base_unit"></div>
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
                                 x-text="selectedProduct?.items_per_bundle + ' ' + selectedProduct?.base_unit"></div>
                        </button>

                    </div>
                </div>

                {{-- Input Jumlah --}}
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Jumlah</p>
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
                        <span class="text-gray-500">Total Unit</span>
                        <span class="font-semibold text-gray-800"
                              x-text="totalUnits + ' ' + (selectedProduct?.base_unit || '')"></span>
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
                <div x-show="totalUnits > (selectedProduct?.stock || 0)"
                     class="flex items-center gap-2 bg-red-50 border border-red-100 rounded-xl px-4 py-3">
                    <span>⚠️</span>
                    <p class="text-red-600 text-xs font-medium">
                        Stok tidak cukup! Tersedia:
                        <span x-text="selectedProduct?.stock + ' ' + selectedProduct?.base_unit"></span>
                    </p>
                </div>

                {{-- Tombol Tambah --}}
                <button @click="addToCart()"
                        :disabled="totalUnits > (selectedProduct?.stock || 0) || totalUnits === 0"
                        :class="totalUnits > (selectedProduct?.stock || 0) || totalUnits === 0
                            ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                            : 'bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-200'"
                        class="w-full py-3.5 rounded-xl text-sm font-bold transition-all">
                    + Tambah ke Keranjang
                </button>

            </div>
        </div>
    </div>
    {{-- Akhir Modal --}}

    {{-- Form Submit Hidden --}}
    <form id="checkout-form" action="{{ route('sales.store') }}" method="POST" style="display:none">
        @csrf
        <div id="checkout-inputs"></div>
    </form>

</div>
{{-- Akhir x-data --}}

@endsection

@push('scripts')
<script>
function pos(products, categories) {
    return {
        products: products,
        categories: categories,
        search: '',
        selectedCategory: '',
        cart: [],
        paymentMethod: '',
        showModal: false,
        selectedProduct: null,
        selectedUnit: 'base',
        modalQty: 1,

        get filteredProducts() {
            return this.products.filter(p => {
                const matchSearch = p.name.toLowerCase().includes(this.search.toLowerCase());
                const matchCat    = this.selectedCategory === '' || p.category === this.selectedCategory;
                return matchSearch && matchCat;
            });
        },

        get totalPrice() {
            return this.cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
        },

        get totalUnits() {
            if (!this.selectedProduct) return 0;
            if (this.selectedUnit === 'base')    return this.modalQty;
            if (this.selectedUnit === 'package') return this.modalQty * this.selectedProduct.items_per_package;
            if (this.selectedUnit === 'bundle')  return this.modalQty * this.selectedProduct.items_per_bundle;
            return 0;
        },

        get unitPrice() {
            if (!this.selectedProduct) return 0;
            return this.selectedProduct.selling_price;
        },

        formatNumber(n) {
            return new Intl.NumberFormat('id-ID').format(n || 0);
        },

        getCategoryIcon(category) {
            const icons = {
                'Sembako':                '🌾',
                'Jajanan / Snack':        '🍿',
                'Kebutuhan Rumah Tangga': '🧴',
                'Minuman':                '🥤',
            };
            return icons[category] || '📦';
        },

        openModal(product) {
            this.selectedProduct = product;
            this.selectedUnit    = 'base';
            this.modalQty        = 1;
            this.showModal       = true;
        },

        selectUnit(unit) {
            this.selectedUnit = unit;
            this.modalQty     = 1;
        },

        addToCart() {
            if (this.totalUnits <= 0) return;
            if (this.totalUnits > this.selectedProduct.stock) return;

            let description = '';
            if (this.selectedUnit === 'base') {
                description = this.modalQty + ' ' + this.selectedProduct.base_unit;
            } else if (this.selectedUnit === 'package') {
                const label = this.selectedProduct.base_unit === 'KG' ? 'Karung' : 'Package';
                description = this.modalQty + ' ' + label + ' (' + this.totalUnits + ' ' + this.selectedProduct.base_unit + ')';
            } else if (this.selectedUnit === 'bundle') {
                description = this.modalQty + ' Bundle (' + this.totalUnits + ' ' + this.selectedProduct.base_unit + ')';
            }

            const existing = this.cart.findIndex(
                i => i.product_id === this.selectedProduct.id && i.description === description
            );

            if (existing >= 0) {
                this.cart[existing].quantity += this.totalUnits;
            } else {
                this.cart.push({
                    product_id:  this.selectedProduct.id,
                    name:        this.selectedProduct.name,
                    quantity:    this.totalUnits,
                    unit_price:  this.selectedProduct.selling_price,
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
            } else {
                this.removeFromCart(index);
            }
        },

        increaseQty(index) {
            const product = this.products.find(p => p.id === this.cart[index].product_id);
            const totalInCart = this.cart
                .filter(i => i.product_id === this.cart[index].product_id)
                .reduce((sum, i) => sum + i.quantity, 0);

            if (totalInCart < product.stock) {
                this.cart[index].quantity++;
            } else {
                alert('Stok tidak mencukupi!');
            }
        },

        clearCart() {
            if (confirm('Kosongkan keranjang?')) {
                this.cart = [];
                this.paymentMethod = '';
            }
        },

        checkout() {
            if (this.cart.length === 0) return;
            if (!this.paymentMethod) {
                alert('Pilih metode pembayaran terlebih dahulu!');
                return;
            }

            const container = document.getElementById('checkout-inputs');
            container.innerHTML = '';

            const pmInput = document.createElement('input');
            pmInput.type  = 'hidden';
            pmInput.name  = 'payment_method';
            pmInput.value = this.paymentMethod;
            container.appendChild(pmInput);

            this.cart.forEach((item, index) => {
                const fields = {
                    [`items[${index}][product_id]`]:  item.product_id,
                    [`items[${index}][quantity]`]:     item.quantity,
                    [`items[${index}][unit_price]`]:   item.unit_price,
                    [`items[${index}][description]`]:  item.description,
                };
                Object.entries(fields).forEach(([name, value]) => {
                    const input = document.createElement('input');
                    input.type  = 'hidden';
                    input.name  = name;
                    input.value = value;
                    container.appendChild(input);
                });
            });

            document.getElementById('checkout-form').submit();
        }
    }
}
</script>
@endpush