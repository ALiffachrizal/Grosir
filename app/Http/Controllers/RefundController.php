<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Refund;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\StockLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundController extends Controller
{
    /**
     * Menampilkan daftar transaksi yang dapat direfund.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        if ($request->filled('search')) {
            $sales = Sale::with([
                'user',
                'details.product',
                'refunds',
            ])
                ->where('id', $request->search)
                ->orWhereHas('details.product', function ($query) use ($search) {
                    $query->where(
                        'name',
                        'like',
                        '%' . $search . '%'
                    );
                })
                ->latest()
                ->take(10)
                ->get();
        } else {
            $sales = Sale::with([
                'user',
                'details.product',
                'refunds',
            ])
                ->latest()
                ->take(10)
                ->get();
        }

        return view('refunds.index', compact(
            'sales',
            'search'
        ));
    }

    /**
     * Menampilkan form refund berdasarkan transaksi penjualan.
     */
    public function create(Request $request)
    {
        $request->validate([
            'sale_id' => [
                'required',
                'exists:sales,id',
            ],
        ], [
            'sale_id.required' => 'Transaksi penjualan wajib dipilih.',
            'sale_id.exists' => 'Transaksi penjualan tidak ditemukan.',
        ]);

        $sale = Sale::with([
            'user',
            'details.product',
            'refunds',
        ])->findOrFail($request->sale_id);

        /*
        |--------------------------------------------------------------------------
        | Gabungkan detail berdasarkan kode produk
        |--------------------------------------------------------------------------
        |
        | Satu produk dapat muncul beberapa kali pada sale_details, misalnya
        | dibeli sebagai satuan dan package. Karena tabel refund menyimpan
        | kode_produk, jumlah pembeliannya harus digabung terlebih dahulu.
        |
        */
        $refundableItems = $sale->details
            ->groupBy('kode_produk')
            ->map(function ($details, $kodeProduk) use ($sale) {
                $firstDetail = $details->first();

                $purchased = (int) $details->sum('quantity');

                $refunded = (int) $sale->refunds
                    ->where('kode_produk', $kodeProduk)
                    ->sum('quantity');

                return [
                    'kode_produk' => $kodeProduk,

                    'product' => $firstDetail->product,

                    'purchased' => $purchased,

                    'refunded' => $refunded,

                    'refundable' => max(
                        0,
                        $purchased - $refunded
                    ),

                    'unit_price' => $firstDetail->unit_price,

                    'unit_price_formatted' =>
                        $firstDetail->unit_price_formatted,

                    'descriptions' => $details
                        ->pluck('description')
                        ->filter()
                        ->unique()
                        ->values(),
                ];
            })
            ->filter(function (array $item) {
                return $item['refundable'] > 0;
            })
            ->values();

        if ($refundableItems->isEmpty()) {
            return redirect()
                ->route('refunds.index')
                ->with(
                    'warning',
                    'Semua produk dalam transaksi ini sudah direfund.'
                );
        }

        return view('refunds.create', compact(
            'sale',
            'refundableItems'
        ));
    }

    /**
     * Menyimpan proses refund.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => [
                'required',
                'exists:sales,id',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.kode_produk' => [
                'required',
                'string',
                'distinct',
                'exists:products,kode_produk',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ], [
            'sale_id.required' =>
                'Transaksi penjualan wajib dipilih.',

            'sale_id.exists' =>
                'Transaksi penjualan tidak ditemukan.',

            'items.required' =>
                'Pilih minimal satu produk untuk direfund.',

            'items.array' =>
                'Data produk refund tidak valid.',

            'items.min' =>
                'Pilih minimal satu produk untuk direfund.',

            'items.*.kode_produk.required' =>
                'Produk refund wajib dipilih.',

            'items.*.kode_produk.distinct' =>
                'Produk yang sama tidak boleh dikirim lebih dari satu kali.',

            'items.*.kode_produk.exists' =>
                'Produk refund tidak ditemukan.',

            'items.*.quantity.required' =>
                'Jumlah refund wajib diisi.',

            'items.*.quantity.integer' =>
                'Jumlah refund harus berupa angka bulat.',

            'items.*.quantity.min' =>
                'Jumlah refund minimal satu unit.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Rapikan item refund
        |--------------------------------------------------------------------------
        |
        | Semua quantity diubah menjadi integer dan diurutkan berdasarkan
        | kode produk agar proses penguncian data lebih konsisten.
        |
        */
        $items = collect($validated['items'])
            ->map(function (array $item) {
                return [
                    'kode_produk' => $item['kode_produk'],
                    'quantity' => (int) $item['quantity'],
                ];
            })
            ->sortBy('kode_produk')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Proses refund dalam database transaction
        |--------------------------------------------------------------------------
        |
        | Jika salah satu proses gagal, refund, penambahan stok, dan stock log
        | akan dibatalkan seluruhnya.
        |
        */
        DB::transaction(function () use ($validated, $items) {
            /*
            |--------------------------------------------------------------------------
            | Kunci transaksi penjualan
            |--------------------------------------------------------------------------
            |
            | Penguncian mencegah dua refund dari transaksi yang sama diproses
            | bersamaan dan menyebabkan jumlah refund melebihi pembelian.
            |
            */
            $sale = Sale::query()
                ->lockForUpdate()
                ->findOrFail($validated['sale_id']);

            foreach ($items as $item) {
                $kodeProduk = $item['kode_produk'];
                $requestedQuantity = $item['quantity'];

                /*
                |--------------------------------------------------------------------------
                | Hitung total pembelian produk
                |--------------------------------------------------------------------------
                |
                | Menggunakan sum agar seluruh sale_details dengan kode produk
                | yang sama ikut dihitung.
                |
                */
                $purchasedQuantity = (int) SaleDetail::query()
                    ->where('sale_id', $sale->id)
                    ->where('kode_produk', $kodeProduk)
                    ->sum('quantity');

                if ($purchasedQuantity < 1) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'Produk ' . $kodeProduk .
                            ' tidak terdapat dalam transaksi ini.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Hitung jumlah yang sudah direfund
                |--------------------------------------------------------------------------
                */
                $alreadyRefunded = (int) Refund::query()
                    ->where('sale_id', $sale->id)
                    ->where('kode_produk', $kodeProduk)
                    ->sum('quantity');

                $maxRefundable = max(
                    0,
                    $purchasedQuantity - $alreadyRefunded
                );

                /*
                |--------------------------------------------------------------------------
                | Cegah refund melebihi pembelian
                |--------------------------------------------------------------------------
                */
                if ($requestedQuantity > $maxRefundable) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'Jumlah refund produk ' . $kodeProduk .
                            ' melebihi batas. Maksimal ' .
                            $maxRefundable . ' unit.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Kunci produk sebelum menambah stok
                |--------------------------------------------------------------------------
                */
                $product = Product::query()
                    ->where('kode_produk', $kodeProduk)
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'Produk ' . $kodeProduk .
                            ' tidak ditemukan.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Simpan refund
                |--------------------------------------------------------------------------
                */
                $refund = Refund::create([
                    'sale_id' => $sale->id,
                    'kode_produk' => $kodeProduk,
                    'user_id' => auth()->id(),
                    'quantity' => $requestedQuantity,
                    'date' => Carbon::today(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Kembalikan stok produk
                |--------------------------------------------------------------------------
                */
                $product->increment(
                    'stock',
                    $requestedQuantity
                );

                /*
                |--------------------------------------------------------------------------
                | Simpan riwayat perubahan stok
                |--------------------------------------------------------------------------
                */
                StockLog::create([
                    'kode_produk' => $kodeProduk,
                    'user_id' => auth()->id(),
                    'type' => 'refund',
                    'quantity' => $requestedQuantity,
                    'reference_type' => 'refund',
                    'reference_id' => $refund->id,
                    'note' =>
                        'Refund dari transaksi #' .
                        str_pad(
                            (string) $sale->id,
                            6,
                            '0',
                            STR_PAD_LEFT
                        ),
                ]);
            }
        }, 3);

        return redirect()
            ->route('refunds.index')
            ->with(
                'success',
                'Refund berhasil diproses. Stok telah dikembalikan.'
            );
    }

    /**
     * Menampilkan detail refund dari sebuah transaksi.
     */
    public function show(Sale $sale)
    {
        $sale->load([
            'user',
            'details.product',
            'refunds.product',
            'refunds.user',
        ]);

        return view('refunds.show', compact('sale'));
    }
}