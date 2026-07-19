<?php

namespace App\Http\Controllers;

use App\Models\Category;
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
    /**
     * Jumlah produk yang dimuat saat halaman POS pertama kali dibuka.
     *
     * Sebelumnya SEMUA produk (stock > 0) di-dump ke JavaScript sekaligus
     * lewat @js($products). Untuk katalog besar ini berat di initial load.
     * Sekarang hanya sebagian yang dimuat di awal; sisanya dicari lewat
     * endpoint AJAX searchProducts() di bawah, sesuai ketikan kasir.
     */
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

    /**
     * Menampilkan halaman Point of Sale.
     *
     * Hanya memuat sebagian produk di awal (lihat INITIAL_PRODUCTS_LIMIT).
     * Pencarian produk lain dilakukan lewat endpoint AJAX searchProducts().
     */
    public function create()
    {
        $products = Product::with('category')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->take(self::INITIAL_PRODUCTS_LIMIT)
            ->get();

        $categories = Category::product()
            ->orderBy('name')
            ->get();

        return view('sales.create', compact(
            'products',
            'categories'
        ));
    }

    /**
     * Endpoint AJAX pencarian produk untuk halaman POS.
     *
     * Dipanggil dari JavaScript (Alpine.js) setiap kali kasir mengetik di
     * kolom pencarian atau memilih kategori, dengan debounce di sisi client
     * agar tidak membanjiri server dengan request di setiap ketikan.
     *
     * Query params:
     *   - q: kata kunci nama produk (opsional)
     *   - category: nama kategori (opsional)
     */
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

    /**
     * Menyimpan transaksi penjualan.
     */
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
                'array:kode_produk,quantity,unit_price,description',
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

            /*
             * Field unit_price tetap divalidasi agar cocok dengan form POS
             * yang sekarang. Namun nilai ini tidak digunakan dalam perhitungan.
             * Harga resmi akan diambil kembali dari tabel products.
             */
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
        |
        | Harga yang dikirim browser tidak dimasukkan ke data yang diproses.
        | Controller hanya menggunakan kode produk, quantity, dan description.
        |
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
        |
        | Produk yang sama dapat berada pada beberapa baris keranjang karena
        | dipilih sebagai satuan, package, atau bundle.
        |
        | Semua quantity produk yang sama dijumlahkan terlebih dahulu sebelum
        | dibandingkan dengan stok yang tersedia.
        |
        */
        $requiredStocks = $items
            ->groupBy('kode_produk')
            ->map(function ($productItems) {
                return (int) $productItems->sum('quantity');
            })
            ->sortKeys();

        try {
            $saleId = DB::transaction(function () use (
                $validated,
                $items,
                $requiredStocks
            ) {
                $productCodes = $requiredStocks
                    ->keys()
                    ->values();

                /*
                |--------------------------------------------------------------------------
                | Kunci data produk
                |--------------------------------------------------------------------------
                |
                | lockForUpdate mencegah dua transaksi menggunakan stok yang
                | sama secara bersamaan sebelum transaksi pertama selesai.
                |
                */
                $products = Product::query()
                    ->whereIn('kode_produk', $productCodes)
                    ->orderBy('kode_produk')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('kode_produk');

                /*
                |--------------------------------------------------------------------------
                | Periksa total stok per produk
                |--------------------------------------------------------------------------
                */
                foreach ($requiredStocks as $kodeProduk => $requiredQuantity) {
                    $product = $products->get($kodeProduk);

                    if (!$product) {
                        throw new DomainException(
                            'Produk dengan kode ' .
                            $kodeProduk .
                            ' tidak ditemukan.'
                        );
                    }

                    if ((int) $product->stock < $requiredQuantity) {
                        throw new DomainException(
                            'Stok ' .
                            $product->name .
                            ' tidak mencukupi. Dibutuhkan ' .
                            $requiredQuantity .
                            ' ' .
                            $product->base_unit .
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
                |
                | Nilai unit_price yang dikirim browser tidak digunakan.
                | Harga diambil langsung dari products.selling_price.
                |
                */
                $totalPrice = $items->sum(
                    function (array $item) use ($products) {
                        $product = $products->get(
                            $item['kode_produk']
                        );

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
                    'user_id' => auth()->id(),

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
                | Simpan detail, kurangi stok, dan buat stock log
                |--------------------------------------------------------------------------
                */
                foreach ($items as $item) {
                    $product = $products->get(
                        $item['kode_produk']
                    );

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

                        'unit_price' =>
                            $unitPrice,

                        'description' =>
                            $item['description'] ?: null,
                    ]);

                    /*
                     * Quantity yang dikirim POS sudah dalam jumlah satuan dasar.
                     *
                     * Contoh:
                     * 2 package, isi package 12 PCS
                     * quantity yang dikirim adalah 24 PCS.
                     */
                    $product->decrement(
                        'stock',
                        $item['quantity']
                    );

                    StockLog::create([
                        'kode_produk' =>
                            $product->kode_produk,

                        'user_id' =>
                            auth()->id(),

                        'type' =>
                            'out',

                        'quantity' =>
                            $item['quantity'],

                        'reference_type' =>
                            'sale',

                        'reference_id' =>
                            $sale->id,

                        'note' =>
                            'Penjualan',
                    ]);
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