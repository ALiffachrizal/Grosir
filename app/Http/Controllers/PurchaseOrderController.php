<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function create()
    {
        $suppliers = Supplier::with('category')
            ->orderBy('name')
            ->get();

        $products = Product::with('category')
            ->orderBy('name')
            ->get();

        return view('purchase-orders.create', compact('suppliers', 'products'));
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

        DB::transaction(function () use ($request) {
            $po = PurchaseOrder::create([
                'kode_supplier' => $request->kode_supplier,
                'user_id'       => auth()->id(),
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