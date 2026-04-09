@extends('layouts.app')

@section('title', 'Supplier')
@section('page-title', 'Kelola Supplier')
@section('page-subtitle', 'Manajemen data supplier')

@section('content')

<div class="bg-white rounded-xl shadow">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 border-b border-gray-100 gap-3">
        <div>
            <h3 class="text-gray-800 font-semibold">Daftar Supplier</h3>
            <p class="text-gray-500 text-sm mt-0.5">Total {{ $suppliers->count() }} supplier</p>
        </div>
        <div class="flex gap-3">
            {{-- Filter Kategori --}}
            <form method="GET" action="{{ route('suppliers.index') }}">
                <select name="category" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Kategori</option>
                    @foreach($supplierCategories as $cat)
                    <option value="{{ $cat->name }}" {{ request('category') == $cat->name ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('suppliers.create') }}"
               class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                + Tambah Supplier
            </a>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="text-left px-5 py-3 font-medium">#</th>
                    <th class="text-left px-5 py-3 font-medium">Nama Supplier</th>
                    <th class="text-left px-5 py-3 font-medium">Kategori</th>
                    <th class="text-left px-5 py-3 font-medium">No. Telepon</th>
                    <th class="text-center px-5 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($suppliers as $index => $supplier)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3 text-gray-500">{{ $index + 1 }}</td>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $supplier->name }}</p>
                    </td>
                    <td class="px-5 py-3">
                        <span class="bg-green-100 text-green-700 text-xs px-2.5 py-1 rounded-full font-medium">
                            {{ $supplier->category }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-600">
                        {{ $supplier->phone ?? '-' }}
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('suppliers.show', $supplier) }}"
                               class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                👁️ Detail
                            </a>
                            <a href="{{ route('suppliers.edit', $supplier) }}"
                               class="bg-yellow-50 hover:bg-yellow-100 text-yellow-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                ✏️ Edit
                            </a>
                            <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST"
                                  onsubmit="return confirm('Hapus supplier {{ $supplier->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="bg-red-50 hover:bg-red-100 text-red-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                    🗑️ Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-12 text-gray-400">
                        <div class="text-4xl mb-2">🏭</div>
                        <p>Belum ada supplier terdaftar</p>
                        <a href="{{ route('suppliers.create') }}" class="text-blue-500 text-sm mt-1 inline-block">
                            + Tambah supplier pertama
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection