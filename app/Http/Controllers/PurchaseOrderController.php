<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Category;
use Carbon\Carbon;

class PurchaseOrderController extends Controller
{
    /**
     * Daftar semua purchase order
     */
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'user', 'details'])
            ->latest()
            ->get();

        return view('purchase-orders.index', compact('purchaseOrders'));
    }

    /**
     * Form buat purchase order baru
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products  = Product::orderBy('name')->get();

        return view('purchase-orders.create', compact('suppliers', 'products'));
    }

    /**
     * Simpan purchase order baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'        => ['required', 'exists:suppliers,id'],
            'order_date'         => ['required', 'date'],
            'products'           => ['required', 'array', 'min:1'],
            'products.*.id'      => ['required', 'exists:products,id'],
            'products.*.quantity'=> ['required', 'integer', 'min:1'],
        ], [
            'supplier_id.required'  => 'Supplier wajib dipilih.',
            'supplier_id.exists'    => 'Supplier tidak valid.',
            'order_date.required'   => 'Tanggal order wajib diisi.',
            'products.required'     => 'Minimal 1 produk harus dipilih.',
            'products.min'          => 'Minimal 1 produk harus dipilih.',
        ]);

        DB::transaction(function () use ($request) {

            // Buat purchase order
            $po = PurchaseOrder::create([
                'supplier_id' => $request->supplier_id,
                'user_id'     => auth()->id(),
                'order_date'  => $request->order_date,
                'status'      => 'pending',
            ]);

            // Simpan detail produk
            foreach ($request->products as $item) {
                PurchaseOrderDetail::create([
                    'purchase_order_id' => $po->id,
                    'product_id'        => $item['id'],
                    'quantity'          => $item['quantity'],
                ]);
            }
        });

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order berhasil dibuat.');
    }

    /**
     * Detail purchase order
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'user', 'details.product']);
        return view('purchase-orders.show', compact('purchaseOrder'));
    }
}