<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Toko Grosir IJAD</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md px-6">
        {{-- Logo / Nama Toko --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-yellow-400">🛒 Toko Grosir IJAD</h1>
            <p class="text-gray-400 mt-2 text-sm">Sistem Informasi Manajemen Toko</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">Masuk ke Sistem</h2>

            {{-- Session Error --}}
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Username --}}
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                        Username
                    </label>
                    <input
                        id="username"
                        name="username"
                        type="text"
                        value="{{ old('username') }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent
                               @error('username') border-red-400 @enderror"
                        placeholder="Masukkan username"
                    >
                    @error('username')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password
                    </label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent
                               @error('password') border-red-400 @enderror"
                        placeholder="Masukkan password"
                    >
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center mb-6">
                    <input id="remember_me" name="remember" type="checkbox"
                           class="h-4 w-4 text-yellow-400 border-gray-300 rounded">
                    <label for="remember_me" class="ml-2 text-sm text-gray-600">
                        Ingat saya
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold
                               py-2.5 px-4 rounded-lg transition duration-150 text-sm">
                    Masuk
                </button>
            </form>
        </div>

        <p class="text-center text-gray-500 text-xs mt-6">
            &copy; {{ date('Y') }} Toko Grosir IJAD. All rights reserved.
        </p>
    </div>

</body>
</html>