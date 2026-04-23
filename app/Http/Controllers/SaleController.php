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
    public function index()
    {
        return redirect()->route('sales.create');
    }

    public function create()
    {
        $products   = Product::where('stock', '>', 0)->orderBy('name')->get();
        $categories = Category::product()->orderBy('name')->get();

        return view('sales.create', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.kode_produk'    => ['required', 'exists:products,kode_produk'],
            'items.*.quantity'       => ['required', 'integer', 'min:1'],
            'items.*.unit_price'     => ['required', 'numeric', 'min:0'],
            'items.*.description'    => ['nullable', 'string'],
            'payment_method'         => ['required', 'in:cash,transfer'],
        ]);

        // Validasi stok SEBELUM transaksi
        foreach ($request->items as $item) {
            $product = Product::where('kode_produk', $item['kode_produk'])->first();
            if ($product->stock < $item['quantity']) {
                return back()->with('error',
                    'Stok ' . $product->name . ' tidak mencukupi. ' .
                    'Stok tersedia: ' . $product->stock . ' ' . $product->base_unit
                );
            }
        }

        $saleId = null;

        DB::transaction(function () use ($request, &$saleId) {
            $totalPrice = collect($request->items)->sum(fn($item) =>
                $item['quantity'] * $item['unit_price']
            );

            $sale = Sale::create([
                'user_id'        => auth()->id(),
                'date'           => Carbon::today(),
                'total_price'    => $totalPrice,
                'payment_method' => $request->payment_method,
            ]);

            foreach ($request->items as $item) {
                $product = Product::where('kode_produk', $item['kode_produk'])->first();

                SaleDetail::create([
                    'sale_id'     => $sale->id,
                    'kode_produk' => $item['kode_produk'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'description' => $item['description'] ?? null,
                ]);

                $product->decrement('stock', $item['quantity']);

                StockLog::create([
                    'kode_produk'    => $item['kode_produk'],
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

    public function show(Sale $sale)
    {
        $sale->load(['user', 'details.product', 'refunds']);
        return view('sales.show', compact('sale'));
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['user', 'details.product']);
        return view('sales.receipt', compact('sale'));
    }
}