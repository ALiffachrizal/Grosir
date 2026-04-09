@extends('layouts.app')

@section('title', 'Tambah Supplier')
@section('page-title', 'Tambah Supplier')
@section('page-subtitle', 'Daftarkan supplier baru')

@section('content')

<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow p-6">
        <form action="{{ route('suppliers.store') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Nama --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Supplier <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                              {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }}"
                       placeholder="Masukkan nama supplier" autofocus>
                @error('name')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kategori --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Kategori <span class="text-red-500">*</span>
                </label>
                <select name="category"
                        class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('category') ? 'border-red-400' : 'border-gray-300' }}">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($supplierCategories as $cat)
                    <option value="{{ $cat->name }}" {{ old('category') == $cat->name ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
                @error('category')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- No. Telepon --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    No. Telepon
                </label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Contoh: 08123456789">
            </div>

            {{-- Tombol --}}
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                    Simpan Supplier
                </button>
                <a href="{{ route('suppliers.index') }}"
                   class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-medium transition">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

@endsection