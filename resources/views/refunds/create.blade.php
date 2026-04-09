@extends('layouts.app')

@section('title', 'Form Refund')
@section('page-title', 'Form Refund')
@section('page-subtitle', 'Pilih produk yang akan direfund')

@section('content')

<div class="max-w-3xl mx-auto space-y-6">

    {{-- Info Transaksi --}}
    <div class="bg-white rounded-xl shadow p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-gray-800 text-lg">
                    Transaksi #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}
                </h3>
                <p class="text-gray-500 text-sm">
                    {{ $sale->created_at->locale('id')->isoFormat('D MMMM Y HH:mm') }}
                    · {{ $sale->user->username }}
                    · {{ $sale->payment_method_label }}
                </p>
            </div>
            <span class="font-bold text-xl text-gray-900">{{ $sale->total_price_formatted }}</span>
        </div>
    </div>

    {{-- Form Refund --}}
    <form action="{{ route('refunds.store') }}" method="POST">
        @csrf
        <input type="hidden" name="sale_id" value="{{ $sale->id }}">

        <div class="bg-white rounded-xl shadow">
            <div class="p-5 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Pilih Produk yang Direfund</h3>
                <p class="text-gray-500 text-sm mt-0.5">
                    Centang produk dan isi jumlah yang akan dikembalikan
                </p>
            </div>

            <div class="divide-y divide-gray-50" x-data="refundForm()">

                @foreach($refundableItems as $index => $item)
                <div class="p-5" x-data="{ checked: false, qty: {{ $item['refundable'] }} }">
                    <div class="flex items-start gap-4">

                        {{-- Checkbox --}}
                        <input type="checkbox"
                               x-model="checked"
                               class="mt-1 w-5 h-5 rounded border-gray-300 text-blue-600 cursor-pointer">

                        {{-- Info Produk --}}
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <p class="font-semibold text-gray-800">
                                        {{ $item['detail']->product->name }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $item['detail']->description ?? '-' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-800">
                                        {{ $item['detail']->unit_price_formatted }}
                                    </p>
                                    <p class="text-xs text-gray-400">per unit</p>
                                </div>
                            </div>

                            {{-- Info Qty --}}
                            <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                                <span>Dibeli: <strong>{{ $item['detail']->quantity }}</strong></span>
                                @if($item['refunded'] > 0)
                                <span class="text-orange-500">Sudah direfund: <strong>{{ $item['refunded'] }}</strong></span>
                                @endif
                                <span class="text-green-600">Bisa direfund: <strong>{{ $item['refundable'] }}</strong></span>
                            </div>

                            {{-- Input Qty (aktif jika dicentang) --}}
                            <div x-show="checked" class="flex items-center gap-3">
                                <label class="text-sm text-gray-600">Jumlah refund:</label>
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            @click="qty > 1 ? qty-- : null"
                                            class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200
                                                   text-gray-700 font-bold flex items-center justify-center transition">
                                        −
                                    </button>
                                    <input type="number"
                                           name="items[{{ $index }}][quantity]"
                                           x-model="qty"
                                           min="1"
                                           max="{{ $item['refundable'] }}"
                                           class="w-16 text-center border border-gray-300 rounded-lg py-1.5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <button type="button"
                                            @click="qty < {{ $item['refundable'] }} ? qty++ : null"
                                            class="w-8 h-8 rounded-lg bg-blue-100 hover:bg-blue-200
                                                   text-blue-700 font-bold flex items-center justify-center transition">
                                        +
                                    </button>
                                </div>
                                <span class="text-xs text-gray-400">
                                    maks. {{ $item['refundable'] }}
                                </span>
                            </div>

                            {{-- Hidden input product_id (hanya kirim jika dicentang) --}}
                            <input type="hidden"
                                   name="items[{{ $index }}][product_id]"
                                   value="{{ $item['detail']->product_id }}"
                                   x-bind:disabled="!checked">
                        </div>
                    </div>
                </div>
                @endforeach

            </div>

            {{-- Tombol --}}
            <div class="p-5 border-t border-gray-100 flex gap-3">
                <button type="submit"
                        class="flex-1 bg-orange-500 hover:bg-orange-600 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                    ↩️ Proses Refund
                </button>
                <a href="{{ route('refunds.index') }}"
                   class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-medium transition">
                    Batal
                </a>
            </div>

        </div>
    </form>

</div>

@endsection

@push('scripts')
<script>
function refundForm() {
    return {}
}
</script>
@endpush