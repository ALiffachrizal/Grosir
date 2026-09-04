<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    /**
     * Mengonfirmasi penerimaan barang.
     *
     * Berbeda dari sebelumnya: jumlah yang ditambahkan ke stok TIDAK LAGI
     * selalu sama persis dengan jumlah yang dipesan. Admin bisa mengoreksi
     * jumlah yang benar-benar diterima per produk (misalnya supplier kirim
     * lebih sedikit karena stok mereka habis) — sistem hanya akan menambah
     * stok sesuai jumlah yang benar-benar dimasukkan di form, dan mencatat
     * jumlah pesanan asli tetap tersimpan untuk perbandingan/audit.
     */
    public function confirm(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->route('receiving.index')
                ->with('error', 'Purchase order ini sudah ' . $purchaseOrder->status_label . '.');
        }

        $purchaseOrder->load('details');

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.kode_produk' => ['required', 'string'],
            'items.*.quantity_received' => ['required', 'integer', 'min:0'],
        ], [
            'items.required' => 'Data penerimaan tidak ditemukan.',
            'items.*.quantity_received.required' => 'Jumlah diterima wajib diisi.',
            'items.*.quantity_received.integer' => 'Jumlah diterima harus berupa angka bulat.',
            'items.*.quantity_received.min' => 'Jumlah diterima tidak boleh kurang dari 0.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Petakan input jumlah diterima per kode_produk
        |--------------------------------------------------------------------------
        */
        $receivedMap = collect($validated['items'])
            ->keyBy('kode_produk')
            ->map(fn ($item) => (int) $item['quantity_received']);

        try {
            DB::transaction(function () use ($purchaseOrder, $receivedMap) {

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

                    /*
                    |----------------------------------------------------------------
                    | Validasi jumlah diterima terhadap jumlah yang dipesan
                    |----------------------------------------------------------------
                    | Jumlah diterima TIDAK BOLEH melebihi jumlah yang dipesan —
                    | kalau supplier ternyata kirim lebih banyak dari pesanan,
                    | itu di luar cakupan PO ini dan harus ditangani terpisah
                    | (misalnya sebagai PO/penerimaan baru).
                    */
                    if (! $receivedMap->has($detail->kode_produk)) {
                        throw ValidationException::withMessages([
                            'items' => 'Data penerimaan tidak lengkap untuk salah satu produk.',
                        ]);
                    }

                    $receivedQty = $receivedMap->get($detail->kode_produk);

                    if ($receivedQty > $detail->quantity) {
                        $product = $products->get($detail->kode_produk);

                        throw ValidationException::withMessages([
                            'items' => 'Jumlah diterima untuk "' .
                                ($product->name ?? $detail->kode_produk) .
                                '" tidak boleh melebihi jumlah pesanan (' .
                                $detail->quantity . ').',
                        ]);
                    }

                    $product = $products->get($detail->kode_produk);

                    if (! $product) {
                        throw new \RuntimeException(
                            'Produk dengan kode "' . $detail->kode_produk . '" tidak ditemukan. '
                            . 'Konfirmasi dibatalkan.'
                        );
                    }

                    
                    $detail->update(['quantity_received' => $receivedQty]);

                
                    if ($receivedQty === 0) {
                        continue;
                    }

                    // Tambah stok HANYA sebesar yang benar-benar diterima
                    $product->increment('stock', $receivedQty);

                    $isPartial = $receivedQty < $detail->quantity;

                    $note = 'Penerimaan barang dari ' . $po->supplier->name;

                    if ($isPartial) {
                        $note .= ' (kurang kirim: dipesan ' . $detail->quantity .
                            ', diterima ' . $receivedQty . ' ' . $product->base_unit . ')';
                    }

                    StockLog::create([
                        'kode_produk'    => $product->kode_produk,
                        'user_id'        => auth()->id(),
                        'type'           => 'in',
                        'quantity'       => $receivedQty,
                        'reference_type' => 'purchase_order',
                        'reference_id'   => $po->id,
                        'note'           => $note,
                    ]);
                }

                $po->update(['status' => 'received']);

            }, 3);

        } catch (ValidationException $e) {
            return redirect()->route('receiving.show', $purchaseOrder)
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('receiving.index')
                ->with('error', 'Gagal memproses penerimaan barang: ' . $e->getMessage() . '. Silakan coba lagi.');
        }

        return redirect()->route('receiving.index')
            ->with('success', 'Penerimaan barang berhasil dikonfirmasi. Stok telah diperbarui sesuai jumlah yang diterima.');
    }

    // ==================== CANCEL ====================

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