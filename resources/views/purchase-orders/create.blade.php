@extends('layouts.app')

@section('title', 'Buat Purchase Order')
@section('page-title', 'Buat Purchase Order')
@section('page-subtitle', 'Buat pesanan barang baru')

@section('content')

<div class="bg-white rounded-xl shadow p-6"
     x-data="purchaseOrder({{ $suppliers->toJson() }}, {{ $products->toJson() }})">

    <form action="{{ route('purchase-orders.store') }}" method="POST" @submit="prepareSubmit">
        @csrf

        {{-- Header Form --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">

            {{-- Supplier --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Supplier <span class="text-red-500">*</span>
                </label>
                <select name="supplier_id" x-model="selectedSupplierId"
                        @change="filterProducts()"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Supplier --</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" data-category="{{ $supplier->category }}">
                        {{ $supplier->name }} ({{ $supplier->category }})
                    </option>
                    @endforeach
                </select>
                @error('supplier_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal Order --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tanggal Order <span class="text-red-500">*</span>
                </label>
                <input type="date" name="order_date"
                       value="{{ old('order_date', date('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('order_date')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- Tabel Produk --}}
        <div class="mb-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-800">Daftar Produk</h3>
                <button type="button" @click="addRow()"
                        class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-sm transition">
                    + Tambah Baris
                </button>
            </div>

            {{-- Pesan pilih supplier dulu --}}
            <div x-show="!selectedSupplierId"
                 class="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3 text-sm text-yellow-700 mb-3">
                ⚠️ Pilih supplier terlebih dahulu untuk melihat produk
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="text-left px-4 py-2.5 font-medium">Produk</th>
                            <th class="text-left px-4 py-2.5 font-medium w-32">Package</th>
                            <th class="text-left px-4 py-2.5 font-medium w-32">Bundle</th>
                            <th class="text-left px-4 py-2.5 font-medium w-32">Satuan</th>
                            <th class="text-center px-4 py-2.5 font-medium w-36">Total Unit</th>
                            <th class="text-center px-4 py-2.5 font-medium w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in rows" :key="index">
                            <tr class="border-b border-gray-100">
                                {{-- Pilih Produk --}}
                                <td class="px-4 py-2">
                                    <select x-model="row.product_id"
                                            @change="onProductChange(index)"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">-- Pilih Produk --</option>
                                        <template x-for="product in filteredProducts" :key="product.id">
                                            <option :value="product.id" x-text="product.name"></option>
                                        </template>
                                    </select>
                                </td>

                                {{-- Package --}}
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-1">
                                        <input type="number" x-model.number="row.package"
                                               @input="calculateTotal(index)"
                                               min="0" placeholder="0"
                                               class="w-full px-2 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <span class="text-xs text-gray-400 whitespace-nowrap" x-text="row.package_label || 'Pcs'"></span>
                                    </div>
                                </td>

                                {{-- Bundle --}}
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-1">
                                        <input type="number" x-model.number="row.bundle"
                                               @input="calculateTotal(index)"
                                               min="0" placeholder="0"
                                               class="w-full px-2 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <span class="text-xs text-gray-400">Bndl</span>
                                    </div>
                                </td>

                                {{-- Satuan --}}
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-1">
                                        <input type="number" x-model.number="row.unit"
                                               @input="calculateTotal(index)"
                                               min="0" placeholder="0"
                                               class="w-full px-2 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <span class="text-xs text-gray-400" x-text="row.base_unit || 'Unit'"></span>
                                    </div>
                                </td>

                                {{-- Total Unit --}}
                                <td class="px-4 py-2 text-center">
                                    <div class="bg-blue-50 rounded-lg px-2 py-1.5">
                                        <span class="font-bold text-blue-700" x-text="row.total"></span>
                                        <span class="text-xs text-blue-500" x-text="' ' + (row.base_unit || '')"></span>
                                    </div>
                                </td>

                                {{-- Hapus Baris --}}
                                <td class="px-4 py-2 text-center">
                                    <button type="button" @click="removeRow(index)"
                                            x-show="rows.length > 1"
                                            class="text-red-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded-lg transition">
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>

                    {{-- Total Keseluruhan --}}
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-sm font-semibold text-gray-700 text-right">
                                Total Semua Produk:
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-bold text-gray-800" x-text="grandTotal"></span>
                                <span class="text-xs text-gray-500">unit</span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Hidden inputs untuk submit --}}
            <div id="hidden-inputs"></div>

        </div>

        {{-- Tombol --}}
        <div class="flex gap-3">
            <button type="submit"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                Simpan Purchase Order
            </button>
            <a href="{{ route('purchase-orders.index') }}"
               class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-medium transition">
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
        filteredProducts: [],
        rows: [
            { product_id: '', package: 0, bundle: 0, unit: 0, total: 0, base_unit: '', package_label: '', items_per_package: 1, items_per_bundle: 1 }
        ],

        get grandTotal() {
            return this.rows.reduce((sum, row) => sum + (row.total || 0), 0);
        },

        filterProducts() {
            if (!this.selectedSupplierId) {
                this.filteredProducts = [];
                return;
            }

            // Ambil kategori supplier yang dipilih
            const supplier = this.suppliers.find(s => s.id == this.selectedSupplierId);
            if (!supplier) {
                this.filteredProducts = [];
                return;
            }

            // Filter produk berdasarkan kategori supplier
            this.filteredProducts = this.products.filter(p => p.category === supplier.category);

            // Reset semua baris
            this.rows.forEach(row => {
                row.product_id = '';
                row.package = 0;
                row.bundle = 0;
                row.unit = 0;
                row.total = 0;
                row.base_unit = '';
                row.package_label = '';
            });
        },

        onProductChange(index) {
            const product = this.products.find(p => p.id == this.rows[index].product_id);
            if (product) {
                this.rows[index].base_unit         = product.base_unit;
                this.rows[index].items_per_package = product.items_per_package;
                this.rows[index].items_per_bundle  = product.items_per_bundle || 1;
                this.rows[index].package_label     = product.base_unit === 'KG' ? 'Karung' : 'Package';
            }
            this.calculateTotal(index);
        },

        calculateTotal(index) {
            const row = this.rows[index];
            const fromPackage = (row.package || 0) * (row.items_per_package || 1);
            const fromBundle  = (row.bundle || 0) * (row.items_per_bundle || 1);
            const fromUnit    = (row.unit || 0);
            row.total = fromPackage + fromBundle + fromUnit;
        },

        addRow() {
            this.rows.push({
                product_id: '', package: 0, bundle: 0, unit: 0,
                total: 0, base_unit: '', package_label: '',
                items_per_package: 1, items_per_bundle: 1
            });
        },

        removeRow(index) {
            if (this.rows.length > 1) {
                this.rows.splice(index, 1);
            }
        },

        prepareSubmit() {
            // Buat hidden inputs untuk submit
            const container = document.getElementById('hidden-inputs');
            container.innerHTML = '';

            this.rows.forEach((row, index) => {
                if (row.product_id && row.total > 0) {
                    const idInput = document.createElement('input');
                    idInput.type  = 'hidden';
                    idInput.name  = `products[${index}][id]`;
                    idInput.value = row.product_id;
                    container.appendChild(idInput);

                    const qtyInput = document.createElement('input');
                    qtyInput.type  = 'hidden';
                    qtyInput.name  = `products[${index}][quantity]`;
                    qtyInput.value = row.total;
                    container.appendChild(qtyInput);
                }
            });
        }
    }
}
</script>
@endpush