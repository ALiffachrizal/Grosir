@extends('layouts.app')

@section('title', 'Tambah User')
@section('page-title', 'Tambah User')
@section('page-subtitle', 'Buat akun pengguna baru')

@section('content')

<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow p-6">

        <form action="{{ route('users.store') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Username --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Username <span class="text-red-500">*</span>
                </label>
                <input type="text" name="username" value="{{ old('username') }}"
                       class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                              {{ $errors->has('username') ? 'border-red-400' : 'border-gray-300' }}"
                       placeholder="Masukkan username" autofocus>
                @error('username')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Info Role --}}
            <div class="bg-purple-50 border border-purple-100 rounded-lg px-4 py-3">
                <p class="text-sm text-purple-700">
                    🔑 Role otomatis: <strong>Admin</strong> — memiliki akses penuh ke semua fitur
                </p>
            </div>

            {{-- Password --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Password <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password"
                       class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                              {{ $errors->has('password') ? 'border-red-400' : 'border-gray-300' }}"
                       placeholder="Minimal 6 karakter">
                @error('password')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Konfirmasi Password --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Konfirmasi Password <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password_confirmation"
                       class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 border-gray-300"
                       placeholder="Ulangi password">
            </div>

            {{-- Tombol --}}
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                    Simpan User
                </button>
                <a href="{{ route('users.index') }}"
                   class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-medium transition">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

@endsection