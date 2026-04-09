<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Akses Ditolak</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center">
    <div class="text-center px-6">
        {{-- Icon --}}
        <div class="text-8xl mb-6">🚫</div>

        {{-- Kode Error --}}
        <h1 class="text-6xl font-bold text-yellow-400 mb-4">403</h1>

        {{-- Pesan --}}
        <h2 class="text-2xl font-semibold text-white mb-2">Akses Ditolak</h2>
        <p class="text-gray-400 mb-8 max-w-md mx-auto">
            Anda tidak memiliki izin untuk mengakses halaman ini. 
            Silakan hubungi administrator jika ini adalah kesalahan.
        </p>

        {{-- Info Role --}}
        @auth
        <div class="bg-white/10 rounded-lg px-6 py-3 inline-block mb-8">
            <p class="text-gray-300 text-sm">
                Login sebagai: 
                <span class="font-semibold text-yellow-400">
                    {{ auth()->user()->username }}
                </span>
                <span class="ml-2 text-xs bg-white/20 px-2 py-0.5 rounded-full text-white">
                    {{ auth()->user()->role_label }}
                </span>
            </p>
        </div>
        @endauth

        {{-- Tombol --}}
        <div class="flex gap-3 justify-center">
            <a href="{{ url()->previous() }}"
               class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">
                ← Kembali
            </a>
            <a href="{{ route('dashboard') }}"
               class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 px-6 py-2.5 rounded-lg text-sm font-semibold transition">
                Dashboard
            </a>
        </div>
    </div>
</body>
</html>