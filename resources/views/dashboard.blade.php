@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan informasi toko hari ini')

@section('content')

{{-- STAT CARDS --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

    <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl p-5 text-white shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-purple-200 text-sm font-medium">Total Produk</p>
                <p class="text-3xl font-bold mt-1">{{ $totalProducts }}</p>
                <p class="text-purple-200 text-xs mt-1">produk terdaftar</p>
            </div>
            <div class="text-4xl opacity-80">📦</div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-xl p-5 text-white shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-green-200 text-sm font-medium">Total Supplier</p>
                <p class="text-3xl font-bold mt-1">{{ $totalSuppliers }}</p>
                <p class="text-green-200 text-xs mt-1">supplier aktif</p>
            </div>
            <div class="text-4xl opacity-80">🏭</div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-xl p-5 text-white shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-yellow-100 text-sm font-medium">Pending Orders</p>
                <p class="text-3xl font-bold mt-1">{{ $pendingOrders }}</p>
                <p class="text-yellow-100 text-xs mt-1">menunggu penerimaan</p>
            </div>
            <div class="text-4xl opacity-80">🛒</div>
        </div>
    </div>

    <a href="{{ route('reports.sales') }}?filter=today" class="block">
        <div class="bg-gradient-to-br from-pink-500 to-pink-700 rounded-xl p-5 text-white shadow hover:shadow-lg transition cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-pink-200 text-sm font-medium">Penjualan Hari Ini</p>
                    <p class="text-2xl font-bold mt-1">Rp {{ number_format($todaySales, 0, ',', '.') }}</p>
                    <p class="text-pink-200 text-xs mt-1">klik untuk lihat laporan</p>
                </div>
                <div class="text-4xl opacity-80">💰</div>
            </div>
        </div>
    </a>

</div>

{{-- GRAFIK & STOK MENIPIS --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">

    <div class="xl:col-span-2 bg-white rounded-xl shadow p-5">
        <h3 class="text-gray-800 font-semibold text-base mb-4">📈 Penjualan 7 Hari Terakhir</h3>
        <canvas id="salesChart" height="100"></canvas>
    </div>

    <div class="bg-white rounded-xl shadow p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-gray-800 font-semibold text-base">⚠️ Stok Menipis</h3>
            @if($lowStockProducts->count() > 0)
            <span class="bg-red-100 text-red-700 text-xs font-semibold px-2 py-1 rounded-full">
                {{ $lowStockProducts->count() }} produk
            </span>
            @endif
        </div>

        @if($lowStockProducts->isEmpty())
        <div class="text-center py-8">
            <div class="text-4xl mb-2">✅</div>
            <p class="text-gray-500 text-sm">Semua stok aman</p>
        </div>
        @else
        <div class="space-y-2 overflow-y-auto max-h-64">
            @foreach($lowStockProducts as $product)
            <div class="flex items-center justify-between p-2.5 bg-red-50 rounded-lg border border-red-100">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $product->name }}</p>
                    <p class="text-xs text-gray-500">Min: {{ $product->minimum_stock }} {{ $product->base_unit }}</p>
                </div>
                <div class="text-right">
                    <span class="text-sm font-bold text-red-600">{{ $product->stock }}</span>
                    <p class="text-xs text-gray-500">{{ $product->base_unit }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

{{-- AKSI CEPAT --}}
<div class="bg-white rounded-xl shadow p-5">
    <h3 class="text-gray-800 font-semibold text-base mb-4">⚡ Aksi Cepat</h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">

        <a href="{{ route('sales.create') }}"
           class="flex flex-col items-center gap-2 p-4 bg-blue-50 hover:bg-blue-100 rounded-xl transition text-center">
            <span class="text-3xl">🏪</span>
            <span class="text-sm font-medium text-blue-700">Penjualan Baru</span>
        </a>

        <a href="{{ route('refunds.index') }}"
           class="flex flex-col items-center gap-2 p-4 bg-orange-50 hover:bg-orange-100 rounded-xl transition text-center">
            <span class="text-3xl">↩️</span>
            <span class="text-sm font-medium text-orange-700">Proses Refund</span>
        </a>

        <a href="{{ route('purchase-orders.index') }}"
           class="flex flex-col items-center gap-2 p-4 bg-green-50 hover:bg-green-100 rounded-xl transition text-center">
            <span class="text-3xl">🛒</span>
            <span class="text-sm font-medium text-green-700">Pesan Barang</span>
        </a>

        <a href="{{ route('receiving.index') }}"
           class="flex flex-col items-center gap-2 p-4 bg-purple-50 hover:bg-purple-100 rounded-xl transition text-center">
            <span class="text-3xl">📥</span>
            <span class="text-sm font-medium text-purple-700">Terima Barang</span>
        </a>

        <a href="{{ route('products.index') }}"
           class="flex flex-col items-center gap-2 p-4 bg-yellow-50 hover:bg-yellow-100 rounded-xl transition text-center">
            <span class="text-3xl">📦</span>
            <span class="text-sm font-medium text-yellow-700">Kelola Produk</span>
        </a>

        <a href="{{ route('reports.sales') }}"
           class="flex flex-col items-center gap-2 p-4 bg-pink-50 hover:bg-pink-100 rounded-xl transition text-center">
            <span class="text-3xl">📊</span>
            <span class="text-sm font-medium text-pink-700">Laporan Penjualan</span>
        </a>

    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($salesLabels),
            datasets: [{
                label: 'Penjualan (Rp)',
                data: @json($salesChart),
                backgroundColor: 'rgba(236, 72, 153, 0.1)',
                borderColor: 'rgba(236, 72, 153, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgba(236, 72, 153, 1)',
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endpush