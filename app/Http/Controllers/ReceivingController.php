<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockLog;
use Illuminate\Support\Facades\DB;

class ReceivingController extends Controller
{
    // ==================== INDEX ====================

    public function index()
    {
        $pendingOrders = PurchaseOrder::with(['supplier', 'user', 'details'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('receiving.index', compact('pendingOrders'));
    }

    // ==================== SHOW ====================

    public function show(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->route('receiving.index')
                ->with('error', 'Purchase order ini sudah ' . $purchaseOrder->status_label . '.');
        }

        $purchaseOrder->load(['supplier', 'user', 'details.product']);

        return view('receiving.show', compact('purchaseOrder'));
    }

    // ==================== CONFIRM ====================

    public function confirm(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->route('receiving.index')
                ->with('error', 'Purchase order ini sudah ' . $purchaseOrder->status_label . '.');
        }

        try {
            DB::transaction(function () use ($purchaseOrder) {

                $po = PurchaseOrder::lockForUpdate()
                    ->findOrFail($purchaseOrder->id);

                if ($po->status !== 'pending') {
                    return;
                }

                $po->load(['supplier', 'details']);

                $kodeProdukList = $po->details
                    ->pluck('kode_produk')
                    ->unique()
                    ->sort()
                    ->values();

                $products = Product::whereIn('kode_produk', $kodeProdukList)
                    ->orderBy('kode_produk')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('kode_produk');

                foreach ($po->details as $detail) {
                    $product = $products->get($detail->kode_produk);

                    if (! $product) {
                        throw new \RuntimeException(
                            'Produk dengan kode "' . $detail->kode_produk . '" tidak ditemukan. '
                            . 'Konfirmasi dibatalkan.'
                        );
                    }

                    $product->increment('stock', $detail->quantity);

                    StockLog::create([
                        'kode_produk'    => $product->kode_produk,
                        'user_id'        => auth()->id(),
                        'type'           => 'in',
                        'quantity'       => $detail->quantity,
                        'reference_type' => 'purchase_order',
                        'reference_id'   => $po->id,
                        'note'           => 'Penerimaan barang dari ' . $po->supplier->name,
                    ]);
                }

                $po->update(['status' => 'received']);

            }, 3);

        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('receiving.index')
                ->with('error', 'Gagal memproses penerimaan barang: ' . $e->getMessage() . '. Silakan coba lagi.');
        }

        return redirect()->route('receiving.index')
            ->with('success', 'Penerimaan barang berhasil dikonfirmasi. Stok telah diperbarui.');
    }

    // ==================== CANCEL ====================

    /**
     * Membatalkan Purchase Order yang statusnya masih 'pending'.
     *
     * Dipakai untuk kasus PO yang ternyata tidak jadi dipenuhi
     * (misal supplier kehabisan stok, harga berubah, atau salah input
     * saat membuat PO). PO yang sudah 'received' TIDAK BISA dibatalkan
     * dari sini — karena stoknya sudah terlanjur bertambah, membatalkan
     * PO yang sudah diterima butuh alur tersendiri (retur ke supplier),
     * bukan sekadar ubah status.
     *
     * Memakai lockForUpdate yang sama seperti confirm(), supaya tidak
     * mungkin terjadi PO yang sedang dikonfirmasi (menambah stok) di satu
     * tab, dibatalkan bersamaan dari tab lain.
     */
    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->route('receiving.index')
                ->with('error', 'Purchase order ini sudah ' . $purchaseOrder->status_label . ', tidak bisa dibatalkan.');
        }

        try {
            DB::transaction(function () use ($purchaseOrder) {
                $po = PurchaseOrder::lockForUpdate()
                    ->findOrFail($purchaseOrder->id);

                if ($po->status !== 'pending') {
                    return;
                }

                $po->update(['status' => 'cancelled']);
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('receiving.index')
                ->with('error', 'Gagal membatalkan purchase order. Silakan coba lagi.');
        }

        return redirect()->route('receiving.index')
            ->with('success', 'Purchase order berhasil dibatalkan.');
    }
}