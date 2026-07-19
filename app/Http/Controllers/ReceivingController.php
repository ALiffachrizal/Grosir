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
                ->with('error', 'Purchase order ini sudah diterima.');
        }

        $purchaseOrder->load(['supplier', 'user', 'details.product']);

        return view('receiving.show', compact('purchaseOrder'));
    }

    // ==================== CONFIRM ====================

    public function confirm(PurchaseOrder $purchaseOrder)
    {
        /*
        |--------------------------------------------------------------------------
        | Pengecekan awal (fast-path, sebelum masuk transaksi)
        |--------------------------------------------------------------------------
        | Ini hanya optimisasi ringan — jika PO sudah jelas berstatus received,
        | kita tidak perlu masuk ke dalam transaksi sama sekali.
        | Pengecekan yang AMAN tetap dilakukan di dalam lock di bawah.
        */
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->route('receiving.index')
                ->with('error', 'Purchase order ini sudah diterima.');
        }

        try {
            /*
            |----------------------------------------------------------------------
            | DB::transaction dengan retry 3x
            |----------------------------------------------------------------------
            | Parameter ke-2 (3) berarti Laravel akan otomatis retry transaksi
            | hingga 3 kali jika terjadi deadlock di MySQL. Ini penting karena
            | kita mengunci banyak baris sekaligus (PO + beberapa produk).
            */
            DB::transaction(function () use ($purchaseOrder) {

                /*
                |------------------------------------------------------------------
                | LANGKAH 1: Kunci baris PurchaseOrder
                |------------------------------------------------------------------
                | lockForUpdate() menambahkan "FOR UPDATE" pada query MySQL.
                | Artinya: request lain yang mencoba mengunci baris yang sama
                | akan MENUNGGU sampai transaksi ini selesai.
                |
                | Tanpa ini, dua request (misal double-click tombol konfirmasi)
                | bisa masuk ke sini secara bersamaan, sama-sama melihat status
                | 'pending', dan akhirnya menambah stok dua kali.
                */
                $po = PurchaseOrder::lockForUpdate()
                    ->findOrFail($purchaseOrder->id);

                /*
                |------------------------------------------------------------------
                | LANGKAH 2: Re-check status di dalam lock
                |------------------------------------------------------------------
                | Pengecekan di luar transaksi (di atas) tidak aman karena belum
                | ada kunci baris. Request kedua yang tiba belakangan bisa saja
                | sudah melewatinya.
                |
                | Di sini, setelah row terkunci, kita cek ulang. Jika request
                | pertama sudah mengubah status ke 'received', request kedua
                | akan berhenti di sini dengan aman — tanpa error, tanpa double
                | stok.
                */
                if ($po->status !== 'pending') {
                    return;
                }

                $po->load(['supplier', 'details']);

                /*
                |------------------------------------------------------------------
                | LANGKAH 3: Kunci semua produk sekaligus, urutan konsisten
                |------------------------------------------------------------------
                | Produk dikunci dalam SATU query (bukan satu per satu di loop)
                | dan diurutkan berdasarkan kode_produk sebelum dikunci.
                |
                | Mengapa diurutkan? Deadlock terjadi ketika:
                |   - Transaksi A mengunci PRD001 lalu menunggu PRD002
                |   - Transaksi B mengunci PRD002 lalu menunggu PRD001
                |
                | Dengan urutan yang selalu sama (misal PRD001 → PRD002 → PRD003),
                | dua transaksi tidak akan pernah saling menunggu dalam urutan
                | yang berlawanan.
                */
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

                /*
                |------------------------------------------------------------------
                | LANGKAH 4: Update stok dan catat ke stock_log
                |------------------------------------------------------------------
                */
                foreach ($po->details as $detail) {
                    $product = $products->get($detail->kode_produk);

                    if (! $product) {
                        throw new \RuntimeException(
                            'Produk dengan kode "' . $detail->kode_produk . '" tidak ditemukan. '
                            . 'Konfirmasi dibatalkan.'
                        );
                    }

                    // Tambah stok produk
                    $product->increment('stock', $detail->quantity);

                    // Catat perubahan stok ke audit trail
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

                /*
                |------------------------------------------------------------------
                | LANGKAH 5: Tandai PO sebagai sudah diterima
                |------------------------------------------------------------------
                | Ini dilakukan TERAKHIR, setelah semua stok berhasil diupdate.
                | Jika ada error di langkah sebelumnya, status tidak akan berubah
                | dan seluruh transaksi akan di-rollback oleh DB::transaction.
                */
                $po->update(['status' => 'received']);

            }, 3); // retry hingga 3x jika deadlock

        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('receiving.index')
                ->with('error', 'Gagal memproses penerimaan barang: ' . $e->getMessage() . '. Silakan coba lagi.');
        }

        return redirect()->route('receiving.index')
            ->with('success', 'Penerimaan barang berhasil dikonfirmasi. Stok telah diperbarui.');
    }
}