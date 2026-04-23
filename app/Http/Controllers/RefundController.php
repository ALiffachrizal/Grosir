<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Refund;
use App\Models\Sale;
use App\Models\Product;
use App\Models\StockLog;
use Carbon\Carbon;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        if ($request->filled('search')) {
            $sales = Sale::with(['user', 'details.product', 'refunds'])
                ->where('id', $request->search)
                ->orWhereHas('details.product', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                ->latest()
                ->take(10)
                ->get();
        } else {
            $sales = Sale::with(['user', 'details.product', 'refunds'])
                ->latest()
                ->take(10)
                ->get();
        }

        return view('refunds.index', compact('sales', 'search'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'sale_id' => ['required', 'exists:sales,id'],
        ]);

        $sale = Sale::with(['user', 'details.product', 'refunds'])
            ->findOrFail($request->sale_id);

        $refundableItems = $sale->details->map(function ($detail) use ($sale) {
            $refunded = $sale->refunds
                ->where('kode_produk', $detail->kode_produk)
                ->sum('quantity');

            return [
                'detail'     => $detail,
                'refunded'   => $refunded,
                'refundable' => $detail->quantity - $refunded,
            ];
        })->filter(fn($item) => $item['refundable'] > 0);

        if ($refundableItems->isEmpty()) {
            return redirect()->route('refunds.index')
                ->with('warning', 'Semua produk dalam transaksi ini sudah direfund.');
        }

        return view('refunds.create', compact('sale', 'refundableItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_id'                => ['required', 'exists:sales,id'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.kode_produk'    => ['required', 'exists:products,kode_produk'],
            'items.*.quantity'       => ['required', 'integer', 'min:1'],
        ]);

        $sale = Sale::with(['details', 'refunds'])->findOrFail($request->sale_id);

        $items = collect($request->items)->filter(fn($item) =>
            isset($item['kode_produk']) && isset($item['quantity'])
        );

        if ($items->isEmpty()) {
            return back()->with('error', 'Pilih minimal 1 produk untuk direfund.');
        }

        // Validasi qty
        foreach ($items as $item) {
            $detail = $sale->details->where('kode_produk', $item['kode_produk'])->first();

            if (!$detail) {
                return back()->with('error', 'Produk tidak ditemukan dalam transaksi ini.');
            }

            $alreadyRefunded = $sale->refunds
                ->where('kode_produk', $item['kode_produk'])
                ->sum('quantity');

            $maxRefundable = $detail->quantity - $alreadyRefunded;

            if ($item['quantity'] > $maxRefundable) {
                return back()->with('error',
                    'Jumlah refund melebihi batas. Maksimal: ' . $maxRefundable . ' unit.'
                );
            }
        }

        DB::transaction(function () use ($items, $sale) {
            foreach ($items as $item) {
                Refund::create([
                    'sale_id'     => $sale->id,
                    'kode_produk' => $item['kode_produk'],
                    'user_id'     => auth()->id(),
                    'quantity'    => $item['quantity'],
                    'date'        => Carbon::today(),
                ]);

                Product::where('kode_produk', $item['kode_produk'])
                    ->first()
                    ->increment('stock', $item['quantity']);

                StockLog::create([
                    'kode_produk'    => $item['kode_produk'],
                    'user_id'        => auth()->id(),
                    'type'           => 'refund',
                    'quantity'       => $item['quantity'],
                    'reference_type' => 'refund',
                    'reference_id'   => $sale->id,
                    'note'           => 'Refund dari transaksi #' . str_pad($sale->id, 6, '0', STR_PAD_LEFT),
                ]);
            }
        });

        return redirect()->route('refunds.index')
            ->with('success', 'Refund berhasil diproses. Stok telah dikembalikan.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['user', 'details.product', 'refunds.product', 'refunds.user']);
        return view('refunds.show', compact('sale'));
    }
}