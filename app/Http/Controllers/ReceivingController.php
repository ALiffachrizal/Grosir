<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseOrder;
use App\Models\StockLog;

class ReceivingController extends Controller
{
    /**
     * Daftar purchase order yang pending
     */
    public function index()
    {
        $pendingOrders = PurchaseOrder::with(['supplier', 'user', 'details'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('receiving.index', compact('pendingOrders'));
    }

    /**
     * Detail purchase order sebelum konfirmasi
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        // Pastikan hanya PO pending yang bisa diproses
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->route('receiving.index')
                ->with('error', 'Purchase order ini sudah diterima.');
        }

        $purchaseOrder->load(['supplier', 'user', 'details.product']);

        return view('receiving.show', compact('purchaseOrder'));
    }

    /**
     * Konfirmasi penerimaan barang
     * Stock bertambah + status jadi received + catat stock_logs
     */
    public function confirm(PurchaseOrder $purchaseOrder)
    {
        // Pastikan hanya PO pending yang bisa dikonfirmasi
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->route('receiving.index')
                ->with('error', 'Purchase order ini sudah diterima.');
        }

        $purchaseOrder->load('details.product');

        DB::transaction(function () use ($purchaseOrder) {

            foreach ($purchaseOrder->details as $detail) {
                // Tambah stok produk
                $detail->product->increment('stock', $detail->quantity);

                // Catat di stock_logs
                StockLog::create([
                    'product_id'     => $detail->product_id,
                    'user_id'        => auth()->id(),
                    'type'           => 'in',
                    'quantity'       => $detail->quantity,
                    'reference_type' => 'purchase_order',
                    'reference_id'   => $purchaseOrder->id,
                    'note'           => 'Penerimaan barang dari ' . $purchaseOrder->supplier->name,
                ]);
            }

            // Update status PO menjadi received
            $purchaseOrder->update(['status' => 'received']);
        });

        return redirect()->route('receiving.index')
            ->with('success', 'Penerimaan barang berhasil dikonfirmasi. Stok telah diperbarui.');
    }
}