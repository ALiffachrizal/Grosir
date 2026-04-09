<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\Category;
use App\Models\StockLog;
use Carbon\Carbon;

class SaleController extends Controller
{
    /**
     * Redirect ke halaman POS
     */
    public function index()
    {
        return redirect()->route('sales.create');
    }

    /**
     * Halaman POS
     */
    public function create()
    {
        $products   = Product::where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        $categories = Category::product()->orderBy('name')->get();

        return view('sales.create', compact('products', 'categories'));
    }

    /**
     * Proses penjualan
     */
    public function store(Request $request)
    {
        $request->validate([
            'items'          => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.description'=> ['nullable', 'string'],
            'payment_method' => ['required', 'in:cash,transfer'],
        ], [
            'items.required'        => 'Keranjang belanja kosong.',
            'payment_method.required'=> 'Metode pembayaran wajib dipilih.',
        ]);

        // Validasi stok SEBELUM transaksi
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            if ($product->stock < $item['quantity']) {
                return back()->with('error',
                    'Stok ' . $product->name . ' tidak mencukupi. ' .
                    'Stok tersedia: ' . $product->stock . ' ' . $product->base_unit
                );
            }
        }

        $saleId = null;

        DB::transaction(function () use ($request, &$saleId) {

            // Hitung total harga
            $totalPrice = collect($request->items)->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            // Buat transaksi penjualan
            $sale = Sale::create([
                'user_id'        => auth()->id(),
                'date'           => Carbon::today(),
                'total_price'    => $totalPrice,
                'payment_method' => $request->payment_method,
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                // Simpan detail penjualan
                SaleDetail::create([
                    'sale_id'     => $sale->id,
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'description' => $item['description'] ?? null,
                ]);

                // Kurangi stok
                $product->decrement('stock', $item['quantity']);

                // Catat stock log
                StockLog::create([
                    'product_id'     => $item['product_id'],
                    'user_id'        => auth()->id(),
                    'type'           => 'out',
                    'quantity'       => $item['quantity'],
                    'reference_type' => 'sale',
                    'reference_id'   => $sale->id,
                    'note'           => 'Penjualan',
                ]);
            }

            $saleId = $sale->id;
        });

        return redirect()->route('sales.receipt', $saleId)
            ->with('success', 'Transaksi berhasil disimpan.');
    }

    /**
     * Detail transaksi
     */
    public function show(Sale $sale)
    {
        $sale->load(['user', 'details.product', 'refunds']);
        return view('sales.show', compact('sale'));
    }

    /**
     * Struk penjualan
     */
    public function receipt(Sale $sale)
    {
        $sale->load(['user', 'details.product']);
        return view('sales.receipt', compact('sale'));
    }
}