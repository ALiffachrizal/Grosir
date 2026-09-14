<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Supplier;
use App\Models\Product;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with([
                'supplier.category',
                'user',
                'details.product.category'
            ])
            ->latest()
            ->get();

        return view('purchase-orders.index', compact('purchaseOrders'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::with('category')
            ->orderBy('name')
            ->get();

        $products = Product::with('category')
            ->orderBy('name')
            ->get();

        $prefillProduct = null;
        $prefillSupplier = null;

        if ($request->filled('product')) {
            $prefillProduct = Product::with('category')
                ->where('kode_produk', $request->query('product'))
                ->first();

            if ($prefillProduct) {
                $prefillSupplier = Supplier::with('category')
                    ->where('kode_kategori', $prefillProduct->kode_kategori)
                    ->first();
            }
        } elseif ($request->filled('supplier')) {
            $prefillSupplier = Supplier::with('category')
                ->where('kode_supplier', $request->query('supplier'))
                ->first();
        }

        return view('purchase-orders.create', compact('suppliers', 'products', 'prefillProduct', 'prefillSupplier'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_supplier'          => ['required', 'exists:suppliers,kode_supplier'],
            'order_date'             => ['required', 'date'],
            'products'               => ['required', 'array', 'min:1'],
            'products.*.kode_produk' => ['required', 'exists:products,kode_produk'],
            'products.*.quantity'    => ['required', 'integer', 'min:1'],
        ], [
            'kode_supplier.required' => 'Supplier wajib dipilih.',
            'kode_supplier.exists'   => 'Supplier tidak valid.',
            'order_date.required'    => 'Tanggal order wajib diisi.',
            'products.required'      => 'Minimal 1 produk harus dipilih.',
            'products.min'           => 'Minimal 1 produk harus dipilih.',
        ]);

        // Validasi aturan: Jumlah pesanan minimal harus sebesar minimal stok produk
        $productCodes = collect($request->products)->pluck('kode_produk')->unique();
        $dbProducts = Product::whereIn('kode_produk', $productCodes)->get()->keyBy('kode_produk');

        foreach ($request->products as $index => $item) {
            $product = $dbProducts->get($item['kode_produk']);
            if ($product && $product->minimum_stock > 0) {
                if ((int) $item['quantity'] < (int) $product->minimum_stock) {
                    throw ValidationException::withMessages([
                        "products.{$index}.quantity" => "Jumlah pesanan untuk {$product->name} minimal {$product->minimum_stock} {$product->base_unit} (sesuai batas minimal stok).",
                    ]);
                }
            }
        }

        DB::transaction(function () use ($request) {
            $po = PurchaseOrder::create([
                'kode_supplier' => $request->kode_supplier,
                'username'      => auth()->user()->username,
                'order_date'    => $request->order_date,
                'status'        => 'pending',
            ]);

            foreach ($request->products as $item) {
                PurchaseOrderDetail::create([
                    'purchase_order_id' => $po->id,
                    'kode_produk'       => $item['kode_produk'],
                    'quantity'          => $item['quantity'],
                ]);
            }
        });

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order berhasil dibuat.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier.category',
            'user',
            'details.product.category'
        ]);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }
}