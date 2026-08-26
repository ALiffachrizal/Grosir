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
     * Sejak fitur "Tahan Transaksi" ditambahkan, halaman ini punya 2 mode:
     *
     * 1. Mode normal (tanpa parameter) — keranjang kosong, siap dipakai
     *    untuk pembeli baru. Daftar draft yang masih tertunda tetap
     *    ditampilkan di atas, supaya kasir bisa memilih melanjutkan salah
     *    satunya kalau perlu.
     *
     * 2. Mode melanjutkan draft ($draftSale terisi lewat route model
     *    binding) — keranjang otomatis terisi ulang dengan produk yang
     *    sudah disimpan sebelumnya, kasir tinggal lanjutkan seperti biasa.
     */
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
        |
        | Ditampilkan semua draft (bukan cuma milik kasir yang login),
        | supaya kalau pergantian shift kasir, draft dari kasir sebelumnya
        | tidak "hilang" dari pandangan.
        */
        $drafts = DraftSale::with(['user', 'details.product'])
            ->latest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Siapkan isi keranjang awal kalau sedang melanjutkan draft
        |--------------------------------------------------------------------------
        |
        | Bentuk data disamakan persis dengan struktur item keranjang yang
        | dipakai Alpine.js di halaman POS (addToCart), supaya begitu
        | halaman dibuka, keranjang langsung terisi tanpa perlu klik ulang
        | satu-satu.
        */
        $initialCart = [];
        $resumingDraftId = null;

        if ($draftSale) {
            $draftSale->load('details.product');

            $resumingDraftId = $draftSale->id;

            $initialCart = $draftSale->details->map(function (DraftSaleDetail $detail) {
                return [
                    'kode_produk'   => $detail->kode_produk,
                    'name'          => $detail->product->name ?? $detail->kode_produk,
                    'base_unit'     => $detail->product->base_unit ?? '',
                    'quantity'      => (int) $detail->quantity,
                    'unit_price'    => (float) $detail->unit_price,
                    'description'   => null,
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
     *
     * Kalau transaksi ini berasal dari draft yang dilanjutkan
     * (draft_sale_id terisi), draft sumbernya otomatis dihapus setelah
     * transaksi berhasil dibuat — supaya tidak nyangkut dobel di daftar
     * draft maupun jadi transaksi resmi.
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

            /*
             * Terisi kalau transaksi ini datang dari draft yang
             * dilanjutkan. Boleh kosong untuk transaksi baru biasa.
             */
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

        $draftSaleId = $validated['draft_sale_id'] ?? null;

        try {
            $saleId = DB::transaction(function () use (
                $validated,
                $items,
                $requiredStocks,
                $draftSaleId
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

                /*
                |--------------------------------------------------------------------------
                | Hapus draft sumber (kalau transaksi ini dari draft)
                |--------------------------------------------------------------------------
                |
                | Draft yang sama harus terkunci juga sebelum dihapus, supaya
                | tidak ada kondisi aneh kalau draft yang sama tanpa sengaja
                | diproses dua kali secara bersamaan.
                */
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

    /**
     * Menyimpan keranjang saat ini sebagai draft (transaksi ditahan),
     * lalu kasir bisa langsung melayani pembeli berikutnya.
     *
     * PENTING: stok TIDAK berkurang di sini. Draft murni "menulis catatan"
     * apa saja yang mau dibeli — pengurangan stok baru terjadi nanti saat
     * draft ini benar-benar dibayar lewat store().
     *
     * Kalau draft_sale_id dikirim (artinya kasir sedang melanjutkan draft
     * yang sudah ada, lalu klik "Simpan / Draft" lagi), method ini akan
     * MEMPERBARUI draft yang sama — bukan membuat draft baru yang terpisah.
     * Tanpa penanganan ini, draft lama akan tetap ada TIDAK BERUBAH,
     * ditambah draft baru duplikat dengan isi keranjang terbaru.
     */
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

            /*
            |--------------------------------------------------------------------------
            | Cari draft yang sedang dilanjutkan (kalau ada)
            |--------------------------------------------------------------------------
            |
            | lockForUpdate mencegah dua request "simpan draft" untuk draft
            | yang sama diproses bersamaan (kasus jarang, tapi tetap dijaga
            | konsisten dengan pola locking yang dipakai di seluruh aplikasi).
            */
            $draft = $existingDraftId
                ? DraftSale::lockForUpdate()->find($existingDraftId)
                : null;

            if ($draft) {
                $isUpdate = true;

                $draft->update([
                    'note' => $request->input('note', $draft->note),
                ]);

                // Ganti seluruh isi detail draft dengan isi keranjang
                // terbaru — lebih sederhana dan aman dibanding mencocokkan
                // baris lama satu per satu, karena isi keranjang bisa
                // berubah bebas (barang ditambah, dikurangi, atau diganti
                // sama sekali).
                $draft->details()->delete();
            } else {
                $draft = DraftSale::create([
                    'user_id' => auth()->id(),
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

                DraftSaleDetail::create([
                    'draft_sale_id' => $draft->id,
                    'kode_produk' => $item['kode_produk'],
                    'quantity' => (int) $item['quantity'],

                    // Harga saat ini disimpan sebagai referensi tampilan.
                    // Harga final tetap dihitung ulang saat draft dibayar.
                    'unit_price' => $product
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