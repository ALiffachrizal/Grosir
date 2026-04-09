<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Refund;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\StockLog;
use Carbon\Carbon;

class RefundController extends Controller
{
    /**
     * Halaman cari transaksi untuk refund
     */
    public function index(Request $request)
    {
        $sales = collect();
        $search = $request->search;

        if ($request->filled('search')) {
            // Cari by ID transaksi
            $sales = Sale::with(['user', 'details.product'])
                ->where('id', $request->search)
                ->orWhereHas('details.product', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                ->latest()
                ->take(10)
                ->get();
        } else {
            // Tampil 10 transaksi terbaru
            $sales = Sale::with(['user', 'details.product'])
                ->latest()
                ->take(10)
                ->get();
        }

        return view('refunds.index', compact('sales', 'search'));
    }

    /**
     * Form refund untuk transaksi tertentu
     */
    public function create(Request $request)
    {
        $request->validate([
            'sale_id' => ['required', 'exists:sales,id'],
        ]);

        $sale = Sale::with(['user', 'details.product', 'refunds'])->findOrFail($request->sale_id);

        // Hitung sisa qty yang bisa direfund per produk
        $refundableItems = $sale->details->map(function ($detail) use ($sale) {
            $refunded = $sale->refunds
                ->where('product_id', $detail->product_id)
                ->sum('quantity');

            return [
                'detail'          => $detail,
                'refunded'        => $refunded,
                'refundable'      => $detail->quantity - $refunded,
            ];
        })->filter(fn($item) => $item['refundable'] > 0);

        return view('refunds.create', compact('sale', 'refundableItems'));
    }

    /**
     * Proses refund
     */
    public function store(Request $request)
    {
        $request->validate([
            'sale_id'              => ['required', 'exists:sales,id'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'exists:products,id'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Pilih minimal 1 produk untuk direfund.',
        ]);

        $sale = Sale::with(['details', 'refunds'])->findOrFail($request->sale_id);

        // Validasi quantity refund tidak melebihi yang dibeli
        foreach ($request->items as $item) {
            $detail = $sale->details->where('product_id', $item['product_id'])->first();

            if (!$detail) {
                return back()->with('error', 'Produk tidak ditemukan dalam transaksi ini.');
            }

            $alreadyRefunded = $sale->refunds
                ->where('product_id', $item['product_id'])
                ->sum('quantity');

            $maxRefundable = $detail->quantity - $alreadyRefunded;

            if ($item['quantity'] > $maxRefundable) {
                return back()->with('error',
                    'Jumlah refund melebihi batas. Maksimal: ' . $maxRefundable . ' unit.'
                );
            }
        }

        DB::transaction(function () use ($request, $sale) {

            foreach ($request->items as $item) {
                // Simpan refund
                Refund::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $item['product_id'],
                    'user_id'    => auth()->id(),
                    'quantity'   => $item['quantity'],
                    'date'       => Carbon::today(),
                ]);

                // Tambah stok kembali
                Product::find($item['product_id'])->increment('stock', $item['quantity']);

                // Catat stock log
                StockLog::create([
                    'product_id'     => $item['product_id'],
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

    /**
     * Detail refund
     */
    public function show(Sale $sale)
    {
        $sale->load(['user', 'details.product', 'refunds.product', 'refunds.user']);
        return view('refunds.show', compact('sale'));
    }
}