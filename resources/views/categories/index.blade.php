@extends('layouts.app')

@section('title', 'Kategori')
@section('page-title', 'Kelola Kategori')
@section('page-subtitle', 'Manajemen kategori produk, supplier, dan satuan')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- ===== KATEGORI PRODUK ===== --}}
    <div class="bg-white rounded-xl shadow">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">🏷️ Kategori Produk</h3>
            <p class="text-gray-500 text-sm mt-0.5">{{ $productCategories->count() }} kategori</p>
        </div>

        {{-- Form Tambah --}}
        <div class="p-5 border-b border-gray-100 bg-gray-50">
            <form action="{{ route('categories.store') }}" method="POST" class="space-y-2">
                @csrf
                <input type="hidden" name="type" value="product">
                <div class="flex gap-2">
                    <input type="text"
                           name="kode_kategori"
                           placeholder="KAT001"
                           maxlength="10"
                           required
                           style="text-transform:uppercase"
                           class="w-28 px-3 py-2 border border-gray-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="text"
                            name="name"
                            placeholder="Nama kategori produk..."
                            required
                            style="text-transform:uppercase"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm
                                    focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2
                                   rounded-lg text-sm font-medium transition whitespace-nowrap">
                        + Tambah
                    </button>
                </div>
                <p class="text-xs text-gray-400">Contoh kode: KAT001, KAT002</p>
            </form>
        </div>

        {{-- Daftar --}}
        <div class="divide-y divide-gray-50">
            @forelse($productCategories as $category)
            <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                    <div>
                        <span class="text-sm text-gray-800">{{ $category->name }}</span>
                        <span class="ml-2 text-xs text-gray-400 font-mono bg-gray-100
                                     px-1.5 py-0.5 rounded">
                            {{ $category->kode_kategori }}
                        </span>
                    </div>
                </div>
                <form action="{{ route('categories.destroy', $category) }}" method="POST"
                      onsubmit="return confirm('Hapus kategori {{ $category->name }}?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="text-red-400 hover:text-red-600 text-xs hover:bg-red-50
                                   px-2 py-1 rounded transition">
                        🗑️ Hapus
                    </button>
                </form>
            </div>
            @empty
            <div class="text-center py-8 text-gray-400">
                <p class="text-sm">Belum ada kategori produk</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ===== KATEGORI SUPPLIER ===== --}}
    <div class="bg-white rounded-xl shadow">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">🏭 Kategori Supplier</h3>
            <p class="text-gray-500 text-sm mt-0.5">{{ $supplierCategories->count() }} kategori</p>
        </div>

        {{-- Form Tambah --}}
        <div class="p-5 border-b border-gray-100 bg-gray-50">
            <form action="{{ route('categories.store') }}" method="POST" class="space-y-2">
                @csrf
                <input type="hidden" name="type" value="supplier">
                <div class="flex gap-2">
                    <input type="text"
                           name="kode_kategori"
                           placeholder="SUP001"
                           maxlength="10"
                           required
                           style="text-transform:uppercase"
                           class="w-28 px-3 py-2 border border-gray-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="text"
                           name="name"
                           placeholder="Nama kategori supplier..."
                           required
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2
                                   rounded-lg text-sm font-medium transition whitespace-nowrap">
                        + Tambah
                    </button>
                </div>
                <p class="text-xs text-gray-400">Contoh kode: SUP001, SUP002</p>
            </form>
        </div>

        {{-- Daftar --}}
        <div class="divide-y divide-gray-50">
            @forelse($supplierCategories as $category)
            <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full bg-green-400"></span>
                    <div>
                        <span class="text-sm text-gray-800">{{ $category->name }}</span>
                        <span class="ml-2 text-xs text-gray-400 font-mono bg-gray-100
                                     px-1.5 py-0.5 rounded">
                            {{ $category->kode_kategori }}
                        </span>
                    </div>
                </div>
                <form action="{{ route('categories.destroy', $category) }}" method="POST"
                      onsubmit="return confirm('Hapus kategori {{ $category->name }}?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="text-red-400 hover:text-red-600 text-xs hover:bg-red-50
                                   px-2 py-1 rounded transition">
                        🗑️ Hapus
                    </button>
                </form>
            </div>
            @empty
            <div class="text-center py-8 text-gray-400">
                <p class="text-sm">Belum ada kategori supplier</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ===== KATEGORI SATUAN ===== --}}
    <div class="bg-white rounded-xl shadow lg:col-span-2">
        <div class="p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">📏 Satuan Produk</h3>
            <p class="text-gray-500 text-sm mt-0.5">{{ $unitCategories->count() }} satuan terdaftar</p>
        </div>

        {{-- Form Tambah --}}
        <div class="p-5 border-b border-gray-100 bg-gray-50">
            <form action="{{ route('categories.store') }}" method="POST" class="space-y-2">
                @csrf
                <input type="hidden" name="type" value="unit">
                <div class="flex gap-2 max-w-xl">
                    <input type="text"
                           name="kode_kategori"
                           placeholder="SAT001"
                           maxlength="10"
                           required
                           style="text-transform:uppercase"
                           class="w-28 px-3 py-2 border border-gray-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <input type="text"
                           name="name"
                           placeholder="Nama satuan (contoh: GRAM, LUSIN, KODI...)"
                           required
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <button type="submit"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2
                                   rounded-lg text-sm font-medium transition whitespace-nowrap">
                        + Tambah
                    </button>
                </div>
                <p class="text-xs text-gray-400">Contoh kode: SAT001, SAT002</p>
            </form>
        </div>

        {{-- Satuan Bawaan (hardcoded) --}}
        <div class="p-5 border-b border-gray-100">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                Satuan Dasar (Bawaan Sistem)
            </p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach(\App\Models\Product::getBaseUnits() as $unit)
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
                    <div class="text-2xl mb-2">
                        @if($unit === 'PCS') 📦
                        @elseif($unit === 'BOTOL') 🍶
                        @elseif($unit === 'LITER') 💧
                        @elseif($unit === 'KG') ⚖️
                        @endif
                    </div>
                    <p class="font-bold text-gray-800">{{ $unit }}</p>
                    <p class="text-xs text-gray-400 mt-1">
                        @if($unit === 'PCS') Pieces / Satuan
                        @elseif($unit === 'BOTOL') Botol
                        @elseif($unit === 'LITER') Liter
                        @elseif($unit === 'KG') Kilogram
                        @endif
                    </p>
                    <span class="text-xs bg-gray-200 text-gray-500 px-2 py-0.5 rounded-full mt-2 inline-block">
                        Sistem
                    </span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Satuan Tambahan --}}
        @if($unitCategories->count() > 0)
        <div class="p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                Satuan Tambahan
            </p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($unitCategories as $unit)
                <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 text-center relative">
                    <div class="text-2xl mb-2">📐</div>
                    <p class="font-bold text-gray-800">{{ $unit->name }}</p>
                    <span class="text-xs font-mono text-purple-500 mt-1 block">
                        {{ $unit->kode_kategori }}
                    </span>
                    {{-- Tombol Hapus --}}
                    <form action="{{ route('categories.destroy', $unit) }}" method="POST"
                          onsubmit="return confirm('Hapus satuan {{ $unit->name }}?')"
                          class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="text-red-400 hover:text-red-600 text-xs hover:bg-red-50
                                       px-2 py-1 rounded transition">
                            🗑️ Hapus
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="p-5 text-center text-gray-400">
            <p class="text-sm">Belum ada satuan tambahan</p>
            <p class="text-xs mt-1">Tambahkan satuan baru di form atas</p>
        </div>
        @endif

    </div>

</div>

@endsection