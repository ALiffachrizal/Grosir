<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DraftSale;
use App\Models\DraftSaleDetail;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\StockLog;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class SaleController extends Controller
{

    private const INITIAL_PRODUCTS_LIMIT = 40;

    /**
     * Jumlah maksimal hasil yang dikembalikan per request pencarian AJAX.
     */
    private const SEARCH_RESULTS_LIMIT = 30;

    /**
     * Mengarahkan halaman daftar penjualan ke halaman POS.
     */
    public function index()
    {
        return redirect()->route('sales.create');
    }

 
    public function create(?DraftSale $draftSale = null)
    {
        $products = Product::with('category')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->take(self::INITIAL_PRODUCTS_LIMIT)
            ->get();

        $categories = Category::product()
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Daftar draft yang masih tertunda
        |--------------------------------------------------------------------------
        */
        $drafts = DraftSale::with(['user', 'details.product'])
            ->latest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Siapkan isi keranjang awal kalau sedang melanjutkan draft
        |--------------------------------------------------------------------------
        */
        $initialCart = [];
        $resumingDraftId = null;

        if ($draftSale) {
            $draftSale->load('details.product');

            $resumingDraftId = $draftSale->id;

            $initialCart = $draftSale->details->map(function (DraftSaleDetail $detail) {
                return [
                    'kode_produk'    => $detail->kode_produk,
                    'name'           => $detail->product->name ?? $detail->kode_produk,
                    'base_unit'      => $detail->product->base_unit ?? '',
                    'quantity'       => (int) $detail->quantity,
                    'bonus_quantity' => (int) $detail->bonus_quantity,
                    'unit_price'     => (float) $detail->unit_price,
                    'description'    => null,
                ];
            })->values();
        }

        return view('sales.create', compact(
            'products',
            'categories',
            'drafts',
            'initialCart',
            'resumingDraftId'
        ));
    }

    
    public function searchProducts(Request $request)
    {
        $validated = $request->validate([
            'q'        => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $products = Product::with('category')
            ->where('stock', '>', 0)
            ->when(
                $validated['q'] ?? null,
                fn ($query, $search) => $query->where('name', 'like', "%{$search}%")
            )
            ->when(
                $validated['category'] ?? null,
                fn ($query, $categoryName) => $query->whereHas(
                    'category',
                    fn ($q) => $q->where('name', $categoryName)
                )
            )
            ->orderBy('name')
            ->take(self::SEARCH_RESULTS_LIMIT)
            ->get();

        return response()->json($products);
    }

    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*' => [
                'required',
                'array:kode_produk,quantity,bonus_quantity,unit_price,description',
            ],

            'items.*.kode_produk' => [
                'required',
                'string',
                'exists:products,kode_produk',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.bonus_quantity' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'items.*.unit_price' => [
                'nullable',
                'numeric',
            ],

            'items.*.description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'payment_method' => [
                'required',
                'in:cash,transfer',
            ],

            
            'draft_sale_id' => [
                'nullable',
                'integer',
                'exists:draft_sales,id',
            ],
        ], [
            'items.required' =>
                'Keranjang belanja masih kosong.',

            'items.array' =>
                'Data keranjang tidak valid.',

            'items.min' =>
                'Pilih minimal satu produk.',

            'items.*.kode_produk.required' =>
                'Kode produk wajib diisi.',

            'items.*.kode_produk.exists' =>
                'Salah satu produk tidak ditemukan.',

            'items.*.quantity.required' =>
                'Jumlah produk wajib diisi.',

            'items.*.quantity.integer' =>
                'Jumlah produk harus berupa angka bulat.',

            'items.*.quantity.min' =>
                'Jumlah produk minimal satu unit.',

            'items.*.description.max' =>
                'Keterangan produk maksimal 255 karakter.',

            'payment_method.required' =>
                'Metode pembayaran wajib dipilih.',

            'payment_method.in' =>
                'Metode pembayaran tidak valid.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Rapikan data item
        |--------------------------------------------------------------------------
        */
        $items = collect($validated['items'])
            ->map(function (array $item) {
                return [
                    'kode_produk' => $item['kode_produk'],

                    'quantity' => (int) $item['quantity'],

                    'description' => isset($item['description'])
                        ? trim($item['description'])
                        : null,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Hitung total kebutuhan stok per produk
        |--------------------------------------------------------------------------
        */
        $draftSaleId = $validated['draft_sale_id'] ?? null;

        try {
            $saleId = DB::transaction(function () use (
                $validated,
                $items,
                $draftSaleId
            ) {
                $productCodes = $items
                    ->pluck('kode_produk')
                    ->unique()
                    ->values();

                /*
                |--------------------------------------------------------------------------
                | Kunci data produk
                |--------------------------------------------------------------------------
                */
                $products = Product::query()
                    ->whereIn('kode_produk', $productCodes)
                    ->orderBy('kode_produk')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('kode_produk');

                /*
                |--------------------------------------------------------------------------
                | Hitung bonus dan total stok fisik yang dibutuhkan per item
                |--------------------------------------------------------------------------
                */
                $itemsWithBonus = $items->map(function (array $item) use ($products) {
                    $product = $products->get($item['kode_produk']);

                    if (!$product) {
                        throw new DomainException(
                            'Produk dengan kode ' . $item['kode_produk'] . ' tidak ditemukan.'
                        );
                    }

                    $bonusQuantity = 0;
                    if ($product->has_promo) {
                        $bonusQuantity = intdiv($item['quantity'], (int) $product->promo_min_qty) * (int) $product->promo_bonus_qty;
                    }

                    $item['bonus_quantity'] = $bonusQuantity;
                    $item['total_physical_quantity'] = $item['quantity'] + $bonusQuantity;

                    return $item;
                });

                $requiredPhysicalStocks = $itemsWithBonus
                    ->groupBy('kode_produk')
                    ->map(function ($productItems) {
                        return (int) $productItems->sum('total_physical_quantity');
                    });

                /*
                |--------------------------------------------------------------------------
                | Periksa total stok fisik per produk
                |--------------------------------------------------------------------------
                */
                foreach ($requiredPhysicalStocks as $kodeProduk => $requiredQuantity) {
                    $product = $products->get($kodeProduk);

                    if ((int) $product->stock < $requiredQuantity) {
                        $bonusUnits = $itemsWithBonus->where('kode_produk', $kodeProduk)->sum('bonus_quantity');
                        $bonusNote = $bonusUnits > 0 ? " (termasuk {$bonusUnits} bonus gratis)" : "";

                        throw new DomainException(
                            'Stok ' .
                            $product->name .
                            ' tidak mencukupi. Dibutuhkan ' .
                            $requiredQuantity .
                            ' ' .
                            $product->base_unit .
                            $bonusNote .
                            ', sedangkan stok tersedia hanya ' .
                            $product->stock .
                            ' ' .
                            $product->base_unit .
                            '.'
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Hitung total menggunakan harga database
                |--------------------------------------------------------------------------
                */
                $totalPrice = $itemsWithBonus->sum(
                    function (array $item) use ($products) {
                        $product = $products->get($item['kode_produk']);
                        $unitPrice = (float) $product->selling_price;

                        return $item['quantity'] * $unitPrice;
                    }
                );

                /*
                |--------------------------------------------------------------------------
                | Simpan transaksi utama
                |--------------------------------------------------------------------------
                */
                $sale = Sale::create([
                    'username' => auth()->user()->username,

                    'date' => Carbon::today(),

                    'total_price' => round(
                        $totalPrice,
                        2
                    ),

                    'payment_method' =>
                        $validated['payment_method'],
                ]);

                /*
                |--------------------------------------------------------------------------
                | Simpan detail, kurangi stok (termasuk bonus), dan buat stock log
                |--------------------------------------------------------------------------
                */
                foreach ($itemsWithBonus as $item) {
                    $product = $products->get($item['kode_produk']);

                    /*
                     * Harga jual resmi selalu berasal dari database.
                     */
                    $unitPrice = (float) $product->selling_price;

                    SaleDetail::create([
                        'sale_id' => $sale->id,

                        'kode_produk' =>
                            $product->kode_produk,

                        'quantity' =>
                            $item['quantity'],

                        'bonus_quantity' =>
                            $item['bonus_quantity'],

                        'unit_price' =>
                            $unitPrice,

                        'description' =>
                            $item['description'] ?: null,
                    ]);

                    $totalDeduct = $item['total_physical_quantity'];

                    $product->decrement(
                        'stock',
                        $totalDeduct
                    );

                    $logNote = $item['bonus_quantity'] > 0
                        ? 'Penjualan (' . $item['quantity'] . ' beli + ' . $item['bonus_quantity'] . ' bonus gratis)'
                        : 'Penjualan';

                    StockLog::create([
                        'kode_produk' =>
                            $product->kode_produk,

                        'username' =>
                            auth()->user()->username,

                        'type' =>
                            'out',

                        'quantity' =>
                            $totalDeduct,

                        'reference_type' =>
                            'sale',

                        'reference_id' =>
                            $sale->id,

                        'note' =>
                            $logNote,
                    ]);
                }

                
                if ($draftSaleId) {
                    DraftSale::where('id', $draftSaleId)
                        ->lockForUpdate()
                        ->delete();
                }

                return $sale->id;
            }, 3);
        } catch (DomainException $exception) {
            /*
             * Kesalahan bisnis seperti stok tidak cukup.
             */
            return back()
                ->withInput()
                ->with(
                    'error',
                    $exception->getMessage()
                );
        } catch (Throwable $exception) {
            /*
             * Kesalahan database atau kesalahan tak terduga tetap dicatat
             * pada log Laravel.
             */
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Transaksi gagal disimpan. Silakan periksa kembali data dan stok produk.'
                );
        }

        return redirect()
            ->route('sales.receipt', $saleId)
            ->with(
                'success',
                'Transaksi berhasil disimpan.'
            );
    }

    
    public function storeDraft(Request $request)
    {
        $validated = $request->validate([
            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.kode_produk' => [
                'required',
                'string',
                'exists:products,kode_produk',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.bonus_quantity' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'note' => [
                'nullable',
                'string',
                'max:255',
            ],

            'draft_sale_id' => [
                'nullable',
                'integer',
                'exists:draft_sales,id',
            ],
        ], [
            'items.required' => 'Keranjang masih kosong, tidak ada yang bisa disimpan.',
            'items.min' => 'Pilih minimal satu produk sebelum disimpan sebagai draft.',
            'items.*.kode_produk.exists' => 'Salah satu produk tidak ditemukan.',
            'items.*.quantity.min' => 'Jumlah produk minimal satu unit.',
        ]);

        $isUpdate = false;

        DB::transaction(function () use ($validated, $request, &$isUpdate) {

            $existingDraftId = $validated['draft_sale_id'] ?? null;

            
            $draft = $existingDraftId
                ? DraftSale::lockForUpdate()->find($existingDraftId)
                : null;

            if ($draft) {
                $isUpdate = true;

                $draft->update([
                    'note' => $request->input('note', $draft->note),
                ]);

                
                $draft->details()->delete();
            } else {
                $draft = DraftSale::create([
                    'username' => auth()->user()->username,
                    'note' => $request->input('note'),
                ]);
            }

            $kodeProdukList = collect($validated['items'])
                ->pluck('kode_produk')
                ->unique()
                ->values();

            $products = Product::whereIn('kode_produk', $kodeProdukList)
                ->get()
                ->keyBy('kode_produk');

            foreach ($validated['items'] as $item) {
                $product = $products->get($item['kode_produk']);

                $bonusQuantity = 0;
                if ($product && $product->has_promo) {
                    $bonusQuantity = intdiv((int) $item['quantity'], (int) $product->promo_min_qty) * (int) $product->promo_bonus_qty;
                }

                DraftSaleDetail::create([
                    'draft_sale_id'  => $draft->id,
                    'kode_produk'    => $item['kode_produk'],
                    'quantity'       => (int) $item['quantity'],
                    'bonus_quantity' => $bonusQuantity,
                    'unit_price'     => $product
                        ? (float) $product->selling_price
                        : 0,
                ]);
            }
        });

        return redirect()
            ->route('sales.create')
            ->with(
                'success',
                $isUpdate
                    ? 'Draft berhasil diperbarui.'
                    : 'Transaksi ditahan sebagai draft. Silakan lanjutkan melayani pembeli berikutnya.'
            );
    }

    /**
     * Membuang draft yang tidak jadi dilanjutkan.
     */
    public function destroyDraft(DraftSale $draftSale)
    {
        $draftSale->delete();

        return redirect()
            ->route('sales.create')
            ->with('success', 'Draft berhasil dihapus.');
    }

    /**
     * Menampilkan detail transaksi penjualan.
     */
    public function show(Sale $sale)
    {
        $sale->load([
            'user',
            'details.product.category',
            'refunds.product.category',
        ]);

        return view('sales.show', compact('sale'));
    }

    /**
     * Menampilkan struk transaksi penjualan.
     */
    public function receipt(Sale $sale)
    {
        $sale->load([
            'user',
            'details.product.category',
        ]);

        return view('sales.receipt', compact('sale'));
    }
}