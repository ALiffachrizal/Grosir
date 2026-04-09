<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — Toko Grosir IJAD</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">

    {{-- Overlay mobile --}}
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/50 z-20 lg:hidden">
    </div>

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed top-0 left-0 h-full w-60 bg-gray-900 z-30 transform transition-transform duration-300 ease-in-out lg:translate-x-0">

        {{-- Logo --}}
        <div class="flex items-center justify-center h-16 border-b border-white/10">
            <a href="{{ route('dashboard') }}" class="text-center">
                <h1 class="text-yellow-400 font-bold text-lg leading-tight">🛒 Toko Grosir</h1>
                <p class="text-yellow-300 font-bold text-sm">IJAD</p>
            </a>
        </div>

        {{-- User Info --}}
        <div class="px-4 py-3 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-yellow-400 flex items-center justify-center text-gray-900 font-bold text-sm">
                    {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
                </div>
                <div>
                    <p class="text-white text-sm font-semibold">{{ auth()->user()->username }}</p>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">
                        Admin
                    </span>
                </div>
            </div>
        </div>

        {{-- Navigation Menu --}}
        <nav class="px-3 py-4 space-y-1 overflow-y-auto h-[calc(100vh-140px)]">

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('dashboard') 
                         ? 'border-l-4 border-yellow-400 bg-white/10 text-white font-semibold pl-2' 
                         : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <span class="text-lg">📊</span>
                <span>Dashboard</span>
            </a>

            {{-- Master Data --}}
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider px-3 pt-4 pb-1">Master Data</p>

            <a href="{{ route('categories.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('categories.*') 
                         ? 'border-l-4 border-yellow-400 bg-white/10 text-white font-semibold pl-2' 
                         : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <span class="text-lg">🏷️</span>
                <span>Kategori</span>
            </a>

            <a href="{{ route('suppliers.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('suppliers.*') 
                         ? 'border-l-4 border-yellow-400 bg-white/10 text-white font-semibold pl-2' 
                         : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <span class="text-lg">🏭</span>
                <span>Supplier</span>
            </a>

            <a href="{{ route('products.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('products.*') 
                         ? 'border-l-4 border-yellow-400 bg-white/10 text-white font-semibold pl-2' 
                         : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <span class="text-lg">📦</span>
                <span class="flex-1">Produk</span>
                @if($lowStockCount > 0)
                <span class="bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full min-w-5 text-center">
                    {{ $lowStockCount }}
                </span>
                @endif
            </a>

            {{-- Gudang --}}
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider px-3 pt-4 pb-1">Gudang</p>

            <a href="{{ route('purchase-orders.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('purchase-orders.*') 
                         ? 'border-l-4 border-yellow-400 bg-white/10 text-white font-semibold pl-2' 
                         : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <span class="text-lg">🛒</span>
                <span>Pemesanan Barang</span>
            </a>

            <a href="{{ route('receiving.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('receiving.*') 
                         ? 'border-l-4 border-yellow-400 bg-white/10 text-white font-semibold pl-2' 
                         : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <span class="text-lg">📥</span>
                <span>Penerimaan Barang</span>
            </a>

            <a href="{{ route('stock-logs.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('stock-logs.*') 
                         ? 'border-l-4 border-yellow-400 bg-white/10 text-white font-semibold pl-2' 
                         : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <span class="text-lg">📋</span>
                <span>Stock Log</span>
            </a>

            <a href="{{ route('reports.stock') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('reports.stock') 
                         ? 'border-l-4 border-yellow-400 bg-white/10 text-white font-semibold pl-2' 
                         : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <span class="text-lg">📈</span>
                <span>Laporan Stok</span>
            </a>

            {{-- Transaksi --}}
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider px-3 pt-4 pb-1">Transaksi</p>

            <a href="{{ route('sales.create') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('sales.*') 
                         ? 'border-l-4 border-yellow-400 bg-white/10 text-white font-semibold pl-2' 
                         : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <span class="text-lg">🏪</span>
                <span>Penjualan (POS)</span>
            </a>

            <a href="{{ route('refunds.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('refunds.*') 
                         ? 'border-l-4 border-yellow-400 bg-white/10 text-white font-semibold pl-2' 
                         : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <span class="text-lg">↩️</span>
                <span>Refund</span>
            </a>

            {{-- Laporan --}}
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider px-3 pt-4 pb-1">Laporan</p>

            <a href="{{ route('reports.sales') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
                      {{ request()->routeIs('reports.sales') 
                         ? 'border-l-4 border-yellow-400 bg-white/10 text-white font-semibold pl-2' 
                         : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                <span class="text-lg">💰</span>
                <span>Laporan Penjualan</span>
            </a>

            {{-- Logout --}}
            <div class="pt-4 border-t border-white/10 mt-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm
                                   text-gray-400 hover:text-white hover:bg-red-500/20 transition-all duration-150">
                        <span class="text-lg">🚪</span>
                        <span>Logout</span>
                    </button>
                </form>
            </div>

        </nav>
    </aside>

    {{-- MAIN CONTENT --}}
    <div class="lg:ml-60 min-h-screen flex flex-col">

        {{-- Topbar --}}
        <header class="sticky top-0 z-10 bg-white shadow-sm h-16 flex items-center justify-between px-4 lg:px-6">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div>
                    <h2 class="text-gray-800 font-semibold text-lg">@yield('page-title', 'Dashboard')</h2>
                    @hasSection('page-subtitle')
                    <p class="text-gray-500 text-xs">@yield('page-subtitle')</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden md:block text-right">
                    <p class="text-sm text-gray-600 font-medium">
                        {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                    </p>
                    <p class="text-xs text-gray-400">
                        {{ \Carbon\Carbon::now()->locale('id')->isoFormat('HH:mm') }} WIB
                    </p>
                </div>
                <div class="hidden md:block h-8 w-px bg-gray-200"></div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-yellow-400 flex items-center justify-center text-gray-900 font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->username }}</p>
                        <p class="text-xs text-gray-500">Admin</p>
                    </div>
                </div>
            </div>
        </header>

        {{-- Alert Global --}}
        @if(session('success') || session('error') || session('warning') || session('info'))
        <div class="px-4 lg:px-6 pt-4" x-data="{ show: true }" x-show="show"
             x-init="setTimeout(() => show = false, 3000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            @if(session('success'))
            <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                <span class="text-lg">✅</span>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
            @endif

            @if(session('error'))
            <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <span class="text-lg">❌</span>
                <p class="text-sm font-medium">{{ session('error') }}</p>
            </div>
            @endif

            @if(session('warning'))
            <div class="flex items-center gap-3 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg">
                <span class="text-lg">⚠️</span>
                <p class="text-sm font-medium">{{ session('warning') }}</p>
            </div>
            @endif

            @if(session('info'))
            <div class="flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg">
                <span class="text-lg">ℹ️</span>
                <p class="text-sm font-medium">{{ session('info') }}</p>
            </div>
            @endif

        </div>
        @endif

        {{-- Main Content --}}
        <main class="flex-1 p-4 lg:p-6">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="bg-white border-t border-gray-200 px-6 py-3">
            <p class="text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} Toko Grosir IJAD — Sistem Informasi Manajemen Toko
            </p>
        </footer>

    </div>

    @stack('scripts')
</body>
</html>