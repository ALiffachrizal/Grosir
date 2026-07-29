@extends('layouts.app')

@section('title', 'Kelola User')
@section('page-title', 'Kelola User')
@section('page-subtitle', 'Manajemen akun pengguna sistem')

@section('content')

<div class="bg-white rounded-xl shadow"
     x-data="{ showDeleteModal: false, deleteAction: '', deleteName: '' }">

    {{-- Header --}}
    <div class="flex items-center justify-between p-5 border-b border-gray-100">
        <div>
            <h3 class="text-gray-800 font-semibold">Daftar User</h3>
            <p class="text-gray-500 text-sm mt-0.5">Total {{ $users->count() }} user terdaftar</p>
        </div>
        <a href="{{ route('users.create') }}"
           class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white
                  px-4 py-2 rounded-lg text-sm font-medium transition">
            + Tambah User
        </a>
    </div>

    {{-- Tabel --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="text-left px-5 py-3 font-medium">#</th>
                    <th class="text-left px-5 py-3 font-medium">Username</th>
                    <th class="text-left px-5 py-3 font-medium">Role</th>
                    <th class="text-left px-5 py-3 font-medium">Dibuat</th>
                    <th class="text-center px-5 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $index => $user)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3 text-gray-500">{{ $index + 1 }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-yellow-400 flex items-center
                                        justify-center text-gray-900 font-bold text-sm">
                                {{ strtoupper(substr($user->username, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ $user->username }}</p>
                                @if($user->id === auth()->user()->id)
                                <p class="text-xs text-blue-500">● Anda</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $user->role_color }}">
                            {{ $user->role_label }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-500">
                        {{ $user->created_at->locale('id')->isoFormat('D MMM Y') }}
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('users.edit', $user) }}"
                               class="bg-yellow-50 hover:bg-yellow-100 text-yellow-700
                                      px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                ✏️ Edit
                            </a>
                            @if($user->id !== auth()->user()->id)
                            <button type="button"
                                    @click="deleteAction = '{{ route('users.destroy', $user) }}'; deleteName = '{{ addslashes($user->username) }}'; showDeleteModal = true"
                                    class="bg-red-50 hover:bg-red-100 text-red-700
                                           px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                🗑️ Hapus
                            </button>
                            @else
                            <span class="bg-gray-50 text-gray-400 px-3 py-1.5 rounded-lg
                                         text-xs font-medium cursor-not-allowed">
                                🔒 Akun Anda
                            </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-12 text-gray-400">
                        <div class="text-4xl mb-2">👥</div>
                        <p>Belum ada user terdaftar</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
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
                    Hapus User?
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

</div>

@endsection