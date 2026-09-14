@extends('layouts.app')

@section('title', 'Laporan Stok')
@section('page-title', 'Laporan Stok')
@section('page-subtitle', 'Kondisi stok semua produk')

@section('content')

{{-- Ringkasan --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

    <div class="bg-white rounded-xl shadow p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Produk</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ $products->count() }}</p>
            </div>
            <div class="text-4xl">📦</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Stok Aman</p>
                <p class="text-3xl font-bold text-green-600 mt-1">
                    {{ $products->count() - $lowStockCount }}
                </p>
            </div>
            <div class="text-4xl">✅</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Stok Menipis</p>
                <p class="text-3xl font-bold text-red-600 mt-1">{{ $lowStockCount }}</p>
                <p class="text-xs text-red-400 mt-0.5">perlu restock</p>
            </div>
            <div class="text-4xl">⚠️</div>
        </div>
    </div>

</div>

{{-- Alert Banner jika ada Stok Menipis --}}
@if($lowStockCount > 0)
<div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4 mb-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div class="flex items-start sm:items-center gap-3">
        <span class="text-2xl">⚠️</span>
        <div>
            <p class="font-bold text-red-800 text-sm">
                Perhatian: Ada {{ $lowStockCount }} produk dengan stok menipis (di bawah batas minimum)!
            </p>
            <p class="text-xs text-red-600 mt-0.5">
                Segera buat Purchase Order (PO) ke supplier agar ketersediaan produk di toko tetap terjaga.
            </p>
        </div>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <a href="{{ route('purchase-orders.create') }}"
           class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white px-3.5 py-2 rounded-lg text-xs font-semibold shadow-sm transition">
            <span>🛒</span>
            <span>Buat PO Sekarang</span>
        </a>
    </div>
</div>
@endif

{{-- Tabel --}}
<div class="bg-white rounded-xl shadow"
     x-data="{ search: '', selectedCategory: '', selectedStatus: '' }">

    {{-- Header + Filter --}}
    <div class="p-5 border-b border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-semibold text-gray-800">Detail Stok Produk</h3>
                <p class="text-gray-500 text-sm mt-0.5">Diurutkan: stok menipis duluan</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.stock.excel') }}"
                   class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white
                          px-4 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap shadow-sm">
                    📊 Export Excel
                </a>
            </div>
        </div>

        {{-- Filter & Search --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">

            {{-- Search --}}
            <div class="sm:col-span-1">
                <input type="text" x-model="search"
                       placeholder="🔍 Cari nama produk..."
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Filter Kategori --}}
            <div>
                <select x-model="selectedCategory"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Kategori</option>
                    @foreach($productCategories as $cat)
                    <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Status --}}
            <div>
                <select x-model="selectedStatus"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="menipis">⚠️ Stok Menipis</option>
                    <option value="aman">✅ Stok Aman</option>
                </select>
            </div>

        </div>
    </div>

    {{-- Tabel Data --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="text-left px-5 py-3 font-medium">#</th>
                    <th class="text-left px-5 py-3 font-medium">Nama Produk</th>
                    <th class="text-left px-5 py-3 font-medium">Kategori</th>
                    <th class="text-left px-5 py-3 font-medium">Satuan</th>
                    <th class="text-center px-5 py-3 font-medium">Stok</th>
                    <th class="text-center px-5 py-3 font-medium">Min. Stok</th>
                    <th class="text-right px-5 py-3 font-medium">Harga Beli</th>
                    <th class="text-right px-5 py-3 font-medium">Harga Jual</th>
                    <th class="text-center px-5 py-3 font-medium">Status</th>
                    <th class="text-center px-5 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="table-body">
                @forelse($products as $index => $product)
                <tr class="hover:bg-gray-50 transition product-row
                           {{ $product->stok_menipis ? 'bg-red-50/70 border-l-4 border-l-red-500' : '' }}"
                    data-name="{{ strtolower($product->name) }}"
                    data-category="{{ $product->category->name ?? '-' }}"
                    data-status="{{ $product->stok_menipis ? 'menipis' : 'aman' }}">
                    <td class="px-5 py-3 text-gray-500 row-number">{{ $index + 1 }}</td>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $product->name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $product->kode_produk }}
                            · {{ $product->items_per_package }} {{ $product->base_unit }}/Package
                            @if($product->supplier)
                                · <span class="text-blue-600 font-medium">Supplier: {{ $product->supplier->name }}</span>
                            @endif
                        </p>
                    </td>
                    <td class="px-5 py-3">
                        <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">
                            {{ $product->category->name ?? '-' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $product->base_unit }}</td>
                    <td class="px-5 py-3 text-center">
                        @if($product->stok_menipis)
                            <span class="font-bold text-lg text-red-600 bg-red-100/90 px-2.5 py-0.5 rounded-lg inline-block shadow-sm">
                                {{ $product->stock }}
                            </span>
                        @else
                            <span class="font-bold text-lg text-gray-800">
                                {{ $product->stock }}
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-center text-gray-700 font-medium">
                        {{ $product->minimum_stock }}
                    </td>
                    <td class="px-5 py-3 text-right text-gray-600">
                        {{ $product->purchase_price_formatted }}
                    </td>
                    <td class="px-5 py-3 text-right font-medium text-gray-800">
                        {{ $product->selling_price_formatted }}
                    </td>
                    <td class="px-5 py-3 text-center">
                        @if($product->stok_menipis)
                        <span class="bg-red-100 text-red-700 border border-red-200 text-xs px-2.5 py-1 rounded-full font-bold inline-flex items-center gap-1">
                            ⚠️ Menipis
                        </span>
                        @else
                        <span class="bg-green-100 text-green-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                            ✅ Aman
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-center whitespace-nowrap">
                        @if($product->stok_menipis)
                            <a href="{{ route('purchase-orders.create', ['product' => $product->kode_produk]) }}"
                               class="inline-flex items-center gap-1 bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold shadow-sm transition">
                                Pesan Langsung
                            </a>
                        @else
                            <a href="{{ route('purchase-orders.create', ['product' => $product->kode_produk]) }}"
                               class="inline-flex items-center gap-1 bg-gray-100 hover:bg-blue-50 text-gray-700 hover:text-blue-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                🛒 Pesan PO
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-12 text-gray-400">
                        <div class="text-4xl mb-2">📦</div>
                        <p>Belum ada produk</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Empty state saat filter --}}
        <div id="empty-filter" class="hidden text-center py-12 text-gray-400">
            <div class="text-4xl mb-2">🔍</div>
            <p>Tidak ada produk yang sesuai filter</p>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput     = document.querySelector('input[x-model="search"]');
    const categorySelect  = document.querySelector('select[x-model="selectedCategory"]');
    const statusSelect    = document.querySelector('select[x-model="selectedStatus"]');
    const rows            = document.querySelectorAll('.product-row');
    const emptyFilter     = document.getElementById('empty-filter');

    function filterTable() {
        const search   = searchInput.value.toLowerCase();
        const category = categorySelect.value;
        const status   = statusSelect.value;

        let visibleCount = 0;
        let rowNumber    = 1;

        rows.forEach(row => {
            const name        = row.dataset.name;
            const rowCategory = row.dataset.category;
            const rowStatus   = row.dataset.status;

            const matchSearch   = !search || name.includes(search);
            const matchCategory = !category || rowCategory === category;
            const matchStatus   = !status || rowStatus === status;

            if (matchSearch && matchCategory && matchStatus) {
                row.classList.remove('hidden');
                row.querySelector('.row-number').textContent = rowNumber++;
                visibleCount++;
            } else {
                row.classList.add('hidden');
            }
        });

        emptyFilter.classList.toggle('hidden', visibleCount > 0);
    }

    searchInput.addEventListener('input', filterTable);
    categorySelect.addEventListener('change', filterTable);
    statusSelect.addEventListener('change', filterTable);
});
</script>
@endpush