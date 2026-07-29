@extends('layouts.app')

@section('title', 'Kategori')
@section('page-title', 'Kelola Kategori')
@section('page-subtitle', 'Dipakai bersama oleh produk & supplier')

@section('content')

@php
    $systemUnits = \App\Models\Product::BASE_UNITS_DEFAULT;
    $totalUnits = count($systemUnits) + $unitCategories->count();
@endphp

<div class="space-y-6" x-data="{
    showDeleteModal: false, deleteAction: '', deleteName: '',
    showEditModal: false, editAction: '', editKode: '', editName: ''
}">

    {{-- ========================================================= --}}
    {{-- RINGKASAN --}}
    {{-- ========================================================= --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

        <div class="flex items-center gap-4 rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-5">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-xl text-white shadow-sm shadow-amber-200">
                🏷️
            </div>

            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wider text-amber-700/70">
                    Kategori
                </p>

                <p class="mt-0.5 font-mono text-2xl font-bold tabular-nums text-slate-800">
                    {{ str_pad($categories->count(), 2, '0', STR_PAD_LEFT) }}
                </p>

                <p class="mt-0.5 text-xs text-slate-500">
                    dipakai bersama produk & supplier
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 to-white p-5">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xl text-white shadow-sm shadow-violet-200">
                📏
            </div>

            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wider text-violet-700/70">
                    Satuan Produk
                </p>

                <p class="mt-0.5 font-mono text-2xl font-bold tabular-nums text-slate-800">
                    {{ str_pad($totalUnits, 2, '0', STR_PAD_LEFT) }}
                </p>

                <p class="mt-0.5 text-xs text-slate-500">
                    {{ count($systemUnits) }} bawaan &middot; {{ $unitCategories->count() }} tambahan
                </p>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- 2 PANEL: KATEGORI | SATUAN --}}
    {{-- ========================================================= --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

        {{-- ===================================================== --}}
        {{-- PANEL KATEGORI --}}
        {{-- ===================================================== --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                <div>
                    <h3 class="font-semibold text-slate-800">
                        Kategori
                    </h3>
                    <p class="mt-0.5 text-xs text-slate-400">
                        Satu kategori, dipakai produk &amp; supplier sekaligus
                    </p>
                </div>

                <span class="rounded-full bg-amber-50 px-2.5 py-1 font-mono text-xs font-semibold tabular-nums text-amber-700">
                    {{ $categories->count() }}
                </span>
            </div>

            {{-- Form tambah — gaya "label kosong" yang belum diisi --}}
            <details class="group border-b border-slate-100" @if(old('type') === 'product' && $errors->any()) open @endif>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-3 text-sm font-medium text-amber-700 transition hover:bg-amber-50/60">
                    <span class="flex items-center gap-2">
                        <span class="flex h-5 w-5 items-center justify-center rounded-md bg-amber-500 text-xs font-bold text-white">+</span>
                        Tambah Kategori
                    </span>
                    <svg class="h-4 w-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>

                <div class="space-y-3 border-t border-dashed border-amber-200 bg-amber-50/40 px-5 py-4">
                    <form action="{{ route('categories.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="type" value="product">

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div>
                                <label for="category-kode" class="mb-1 block text-xs font-medium text-slate-600">
                                    Kode <span class="text-rose-500">*</span>
                                </label>
                                <input id="category-kode" type="text" name="kode_kategori"
                                       value="{{ old('type') === 'product' ? old('kode_kategori') : '' }}"
                                       placeholder="KAT005" maxlength="10" required autocomplete="off"
                                       oninput="this.value = this.value.toUpperCase()"
                                       class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 font-mono text-sm uppercase tracking-wide focus:outline-none focus:ring-2 focus:ring-amber-400">
                                @if(old('type') === 'product')
                                    @error('kode_kategori')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                @endif
                            </div>

                            <div class="sm:col-span-2">
                                <label for="category-name" class="mb-1 block text-xs font-medium text-slate-600">
                                    Nama <span class="text-rose-500">*</span>
                                </label>
                                <input id="category-name" type="text" name="name"
                                       value="{{ old('type') === 'product' ? old('name') : '' }}"
                                       placeholder="MAKANAN INSTAN" maxlength="100" required autocomplete="off"
                                       oninput="this.value = this.value.toUpperCase()"
                                       class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-amber-400">
                                @if(old('type') === 'product')
                                    @error('name')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs text-slate-400">Contoh: KAT001, KAT002</p>
                            <button type="submit"
                                    class="rounded-lg bg-amber-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-amber-600">
                                Simpan Kategori
                            </button>
                        </div>
                    </form>
                </div>
            </details>

            {{-- Daftar kategori — gaya label rak, garis putus-putus antar baris --}}
            <div class="divide-y divide-dashed divide-slate-200">
                @forelse($categories as $category)
                    <div class="flex items-center justify-between gap-3 px-5 py-3.5 transition hover:bg-slate-50">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="rounded-md border border-amber-200 bg-amber-50 px-2 py-1 font-mono text-[11px] font-semibold tracking-wide text-amber-700">
                                {{ $category->kode_kategori }}
                            </span>

                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-800">
                                    {{ $category->name }}
                                </p>
                                <p class="mt-0.5 flex items-center gap-2 text-[11px] text-slate-400">
                                    <span>📦 {{ $category->products()->count() }} produk</span>
                                    <span>&middot;</span>
                                    <span>🏭 {{ $category->suppliers()->count() }} supplier</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            <button type="button"
                                    @click="editAction = '{{ route('categories.update', $category) }}'; editKode = '{{ addslashes($category->kode_kategori) }}'; editName = '{{ addslashes($category->name) }}'; showEditModal = true"
                                    class="rounded-lg p-2 text-slate-300 transition hover:bg-amber-50 hover:text-amber-600"
                                    title="Edit">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>

                            <button type="button"
                                    @click="deleteAction = '{{ route('categories.destroy', $category) }}'; deleteName = '{{ addslashes($category->name) }}'; showDeleteModal = true"
                                    class="rounded-lg p-2 text-slate-300 transition hover:bg-rose-50 hover:text-rose-500"
                                    title="Hapus">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center">
                        <div class="mb-2 text-3xl">🏷️</div>
                        <p class="text-sm font-medium text-slate-500">Belum ada kategori</p>
                        <p class="mt-0.5 text-xs text-slate-400">Klik "Tambah Kategori" untuk menambahkan.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- PANEL SATUAN --}}
        {{-- ===================================================== --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                <div>
                    <h3 class="font-semibold text-slate-800">
                        Satuan Produk
                    </h3>
                    <p class="mt-0.5 text-xs text-slate-400">
                        Satuan dasar & tambahan untuk produk
                    </p>
                </div>

                <span class="rounded-full bg-violet-50 px-2.5 py-1 font-mono text-xs font-semibold tabular-nums text-violet-700">
                    {{ $totalUnits }}
                </span>
            </div>

            {{-- Form tambah satuan --}}
            <details class="group border-b border-slate-100" @if(old('type') === 'unit' && $errors->any()) open @endif>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-3 text-sm font-medium text-violet-700 transition hover:bg-violet-50/60">
                    <span class="flex items-center gap-2">
                        <span class="flex h-5 w-5 items-center justify-center rounded-md bg-violet-600 text-xs font-bold text-white">+</span>
                        Tambah Satuan
                    </span>
                    <svg class="h-4 w-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>

                <div class="space-y-3 border-t border-dashed border-violet-200 bg-violet-50/40 px-5 py-4">
                    <form action="{{ route('categories.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="type" value="unit">

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div>
                                <label for="unit-kode" class="mb-1 block text-xs font-medium text-slate-600">
                                    Kode <span class="text-rose-500">*</span>
                                </label>
                                <input id="unit-kode" type="text" name="kode_kategori"
                                       value="{{ old('type') === 'unit' ? old('kode_kategori') : '' }}"
                                       placeholder="SAT001" maxlength="10" required autocomplete="off"
                                       oninput="this.value = this.value.toUpperCase()"
                                       class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 font-mono text-sm uppercase tracking-wide focus:outline-none focus:ring-2 focus:ring-violet-400">
                                @if(old('type') === 'unit')
                                    @error('kode_kategori')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                @endif
                            </div>

                            <div class="sm:col-span-2">
                                <label for="unit-name" class="mb-1 block text-xs font-medium text-slate-600">
                                    Nama <span class="text-rose-500">*</span>
                                </label>
                                <input id="unit-name" type="text" name="name"
                                       value="{{ old('type') === 'unit' ? old('name') : '' }}"
                                       placeholder="DUS, KODI, LUSIN" maxlength="100" required autocomplete="off"
                                       oninput="this.value = this.value.toUpperCase()"
                                       class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-violet-400">
                                @if(old('type') === 'unit')
                                    @error('name')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs text-slate-400">Contoh: SAT001</p>
                            <button type="submit"
                                    class="rounded-lg bg-violet-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-violet-700">
                                Simpan Satuan
                            </button>
                        </div>
                    </form>
                </div>
            </details>

            {{-- Satuan bawaan --}}
            <div class="border-b border-slate-100 px-5 py-4">
                <p class="mb-3 text-xs font-medium uppercase tracking-wider text-slate-400">
                    Bawaan &middot; tidak dapat dihapus
                </p>

                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach($systemUnits as $unit)
                        <div class="flex flex-col items-center gap-1.5 rounded-xl border border-slate-100 bg-slate-50 py-3">
                            <span class="text-lg">
                                @if($unit === 'PCS') 📦
                                @elseif($unit === 'BOTOL') 🍶
                                @elseif($unit === 'LITER') 💧
                                @elseif($unit === 'KG') ⚖️
                                @else 📏
                                @endif
                            </span>
                            <span class="font-mono text-xs font-bold text-slate-700">{{ $unit }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Satuan tambahan --}}
            <div class="divide-y divide-dashed divide-slate-200">
                @forelse($unitCategories as $unit)
                    <div class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-slate-50">
                        <div class="flex items-center gap-3">
                            <span class="rounded-md border border-violet-200 bg-violet-50 px-2 py-1 font-mono text-[11px] font-semibold text-violet-700">
                                {{ $unit->kode_kategori }}
                            </span>
                            <span class="text-sm font-semibold text-slate-800">{{ $unit->name }}</span>
                        </div>

                        <div class="flex items-center gap-1">
                            <button type="button"
                                    @click="editAction = '{{ route('categories.update', $unit) }}'; editKode = '{{ addslashes($unit->kode_kategori) }}'; editName = '{{ addslashes($unit->name) }}'; showEditModal = true"
                                    class="rounded-lg p-2 text-slate-300 transition hover:bg-amber-50 hover:text-amber-600" title="Edit">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>

                            <button type="button"
                                    @click="deleteAction = '{{ route('categories.destroy', $unit) }}'; deleteName = '{{ addslashes($unit->name) }}'; showDeleteModal = true"
                                    class="rounded-lg p-2 text-slate-300 transition hover:bg-rose-50 hover:text-rose-500" title="Hapus">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center">
                        <div class="mb-2 text-3xl">📏</div>
                        <p class="text-sm font-medium text-slate-500">Belum ada satuan tambahan</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- FORM TERSEMBUNYI --}}
    {{-- ========================================================= --}}
    <form x-ref="deleteForm" :action="deleteAction" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    {{-- ========================================================= --}}
    {{-- MODAL KONFIRMASI HAPUS --}}
    {{-- ========================================================= --}}
    <div
    x-show="showDeleteModal"
    x-cloak
    @keydown.escape.window="showDeleteModal = false"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="display: none;"
>
    <div
        x-show="showDeleteModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="showDeleteModal = false"
        class="absolute inset-0 bg-gray-900/50"
    ></div>

    <div
        x-show="showDeleteModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6"
    >
        <div class="flex flex-col items-center text-center">

            <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center text-3xl mb-4">
                🗑️
            </div>

            <h3 class="text-lg font-bold text-gray-800">
                Hapus Data Ini?
            </h3>

            <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                Yakin ingin menghapus
                <span class="font-semibold text-gray-700" x-text="deleteName"></span>?
                Tindakan ini tidak dapat dibatalkan.
            </p>

            <div class="flex gap-3 w-full mt-6">
                <button
                    type="button"
                    @click="showDeleteModal = false"
                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700
                           py-2.5 rounded-xl text-sm font-semibold transition"
                >
                    Batal
                </button>

                <button
                    type="button"
                    @click="$refs.deleteForm.submit()"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white
                           py-2.5 rounded-xl text-sm font-semibold shadow-sm transition"
                >
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- FORM TERSEMBUNYI — EDIT --}}
{{-- ========================================================= --}}
<form x-ref="editForm" :action="editAction" method="POST" class="hidden">
    @csrf
    @method('PUT')
    <input type="hidden" name="kode_kategori" :value="editKode">
    <input type="hidden" name="name" :value="editName">
</form>

{{-- ========================================================= --}}
{{-- MODAL EDIT --}}
{{-- ========================================================= --}}
<div
    x-show="showEditModal"
    x-cloak
    @keydown.escape.window="showEditModal = false"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="display: none;"
>
    <div
        x-show="showEditModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="showEditModal = false"
        class="absolute inset-0 bg-gray-900/50"
    ></div>

    <div
        x-show="showEditModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6"
    >
        <div class="flex flex-col items-center text-center">

            <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center text-3xl mb-4">
                ✏️
            </div>

            <h3 class="text-lg font-bold text-gray-800">
                Edit Data
            </h3>

            <p class="text-sm text-gray-500 mt-1 mb-4">
                Ubah kode atau nama, lalu simpan.
            </p>

            <div class="w-full text-left space-y-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">
                        Kode
                    </label>
                    <input type="text"
                           x-model="editKode"
                           maxlength="10"
                           oninput="this.value = this.value.toUpperCase()"
                           class="h-10 w-full rounded-lg border border-slate-300 px-3 font-mono text-sm uppercase tracking-wide focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">
                        Nama
                    </label>
                    <input type="text"
                           x-model="editName"
                           maxlength="100"
                           oninput="this.value = this.value.toUpperCase()"
                           class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
            </div>

            <div class="flex gap-3 w-full mt-6">
                <button
                    type="button"
                    @click="showEditModal = false"
                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700
                           py-2.5 rounded-xl text-sm font-semibold transition"
                >
                    Batal
                </button>

                <button
                    type="button"
                    @click="$refs.editForm.submit()"
                    class="flex-1 bg-amber-500 hover:bg-amber-600 text-white
                           py-2.5 rounded-xl text-sm font-semibold shadow-sm transition"
                >
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>

</div>

@endsection