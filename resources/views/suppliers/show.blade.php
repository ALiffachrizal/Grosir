@extends('layouts.app')

@section('title', 'Detail Supplier')
@section('page-title', 'Detail Supplier')
@section('page-subtitle', 'Informasi lengkap supplier')

@section('content')

<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow p-6">

        <div class="space-y-4">
            <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
                <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center text-2xl">
                    🏭
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">{{ $supplier->name }}</h3>
                    <span class="bg-green-100 text-green-700 text-xs px-2.5 py-1 rounded-full font-medium">
                        {{ $supplier->category }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 mb-1">No. Telepon</p>
                    <p class="text-sm font-medium text-gray-800">{{ $supplier->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Total Pemesanan</p>
                    <p class="text-sm font-medium text-gray-800">{{ $supplier->purchase_orders_count }} order</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Terdaftar</p>
                    <p class="text-sm font-medium text-gray-800">
                        {{ $supplier->created_at->locale('id')->isoFormat('D MMMM Y') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <a href="{{ route('suppliers.edit', $supplier) }}"
               class="flex-1 text-center bg-yellow-400 hover:bg-yellow-500 text-gray-900 py-2.5 rounded-lg text-sm font-semibold transition">
                ✏️ Edit
            </a>
            <a href="{{ route('suppliers.index') }}"
               class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-medium transition">
                ← Kembali
            </a>
        </div>

    </div>
</div>

@endsection