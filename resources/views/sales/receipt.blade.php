<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $sale->id }} — Toko Grosir IJAD</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white w-80 rounded-xl shadow-lg p-6">

        {{-- Header Struk --}}
        <div class="text-center border-b border-dashed border-gray-300 pb-4 mb-4">
            <h1 class="text-xl font-bold text-gray-800">🛒 Toko Grosir IJAD</h1>
            <p class="text-xs text-gray-500 mt-1">Struk Pembelian</p>
        </div>

        {{-- Info Transaksi --}}
        <div class="font-mono text-xs space-y-1 mb-4">
            <div class="flex justify-between">
                <span class="text-gray-500">No. Transaksi</span>
                <span class="font-bold">#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Tanggal</span>
                <span>{{ $sale->created_at->locale('id')->isoFormat('D MMM Y HH:mm') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Kasir</span>
                <span>{{ $sale->user->username }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Pembayaran</span>
                <span>{{ $sale->payment_method_label }}</span>
            </div>
        </div>

        {{-- Daftar Produk --}}
        <div class="border-t border-dashed border-gray-300 pt-4 mb-4">
            <div class="font-mono text-xs space-y-2">
                @foreach($sale->details as $detail)
                <div>
                    <p class="font-semibold text-gray-800">{{ $detail->product->name }}</p>
                    <p class="text-gray-500">{{ $detail->description }}</p>
                    <div class="flex justify-between mt-0.5">
                        <span class="text-gray-500">
                            {{ $detail->quantity }} x Rp {{ number_format($detail->unit_price, 0, ',', '.') }}
                        </span>
                        <span class="font-bold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Total --}}
        <div class="border-t border-dashed border-gray-300 pt-4 mb-6">
            <div class="flex justify-between font-mono">
                <span class="font-bold text-gray-800">TOTAL</span>
                <span class="font-bold text-lg text-gray-900">
                    Rp {{ number_format($sale->total_price, 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center text-xs text-gray-400 border-t border-dashed border-gray-300 pt-4">
            <p>Terima kasih telah berbelanja!</p>
            <p class="mt-1">Barang yang sudah dibeli</p>
            <p>tidak dapat dikembalikan</p>
        </div>

        {{-- Tombol Aksi --}}
        <div class="mt-6 space-y-2 no-print">
            <button onclick="window.print()"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                🖨️ Cetak Struk
            </button>
            <a href="{{ route('sales.create') }}"
               class="block w-full text-center bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                + Transaksi Baru
            </a>
            <a href="{{ route('dashboard') }}"
               class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-medium transition">
                Dashboard
            </a>
        </div>

    </div>

</body>
</html>