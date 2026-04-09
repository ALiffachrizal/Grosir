<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Product;
use App\Models\Category;
use App\Models\StockLog;

class ProductController extends Controller
{
    /**
     * Daftar semua produk
     */
    public function index(Request $request)
    {
        $query = Product::orderBy('name');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('low_stock')) {
            $query->whereColumn('stock', '<=', 'minimum_stock');
        }

        $products          = $query->get();
        $productCategories = Category::product()->orderBy('name')->get();

        return view('products.index', compact('products', 'productCategories'));
    }

    /**
     * Form tambah produk
     */
    public function create()
    {
        $productCategories = Category::product()->orderBy('name')->get();
        $baseUnits         = Product::BASE_UNITS;
        return view('products.create', compact('productCategories', 'baseUnits'));
    }

    /**
     * Simpan produk baru
     */
    public function store(Request $request)
    {
        $validCategories = Category::product()->pluck('name')->toArray();

        $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'category'          => ['required', Rule::in($validCategories)],
            'base_unit'         => ['required', Rule::in(Product::BASE_UNITS)],
            'items_per_package' => ['required', 'integer', 'min:1'],
            'items_per_bundle'  => ['nullable', 'integer', 'min:1'],
            'stock'             => ['nullable', 'integer', 'min:0'],
            'minimum_stock'     => ['required', 'integer', 'min:0'],
            'purchase_price'    => ['required', 'numeric', 'min:0'],
            'selling_price'     => ['required', 'numeric', 'min:0'],
        ], [
            'name.required'              => 'Nama produk wajib diisi.',
            'category.required'          => 'Kategori wajib dipilih.',
            'category.in'                => 'Kategori tidak valid.',
            'base_unit.required'         => 'Satuan dasar wajib dipilih.',
            'base_unit.in'               => 'Satuan dasar tidak valid.',
            'items_per_package.required' => 'Jumlah per package wajib diisi.',
            'items_per_package.min'      => 'Jumlah per package minimal 1.',
            'minimum_stock.required'     => 'Stok minimum wajib diisi.',
            'purchase_price.required'    => 'Harga beli wajib diisi.',
            'selling_price.required'     => 'Harga jual wajib diisi.',
        ]);

        $product = Product::create([
            'name'              => $request->name,
            'category'          => $request->category,
            'base_unit'         => $request->base_unit,
            'items_per_package' => $request->items_per_package,
            'items_per_bundle'  => $request->items_per_bundle ?? 1,
            'stock'             => $request->stock ?? 0,
            'minimum_stock'     => $request->minimum_stock,
            'purchase_price'    => $request->purchase_price,
            'selling_price'     => $request->selling_price,
        ]);

        // Catat di stock_logs jika stok awal > 0
        if ($product->stock > 0) {
            StockLog::create([
                'product_id'     => $product->id,
                'user_id'        => auth()->id(),
                'type'           => 'in',
                'quantity'       => $product->stock,
                'reference_type' => 'initial_stock',
                'reference_id'   => $product->id,
                'note'           => 'Stok awal produk',
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Produk "' . $request->name . '" berhasil ditambahkan.');
    }

    /**
     * Detail produk
     */
    public function show(Product $product)
    {
        $product->load(['stockLogs' => function ($q) {
            $q->with('user')->latest()->take(10);
        }]);

        return view('products.show', compact('product'));
    }

    /**
     * Form edit produk
     */
    public function edit(Product $product)
    {
        $productCategories = Category::product()->orderBy('name')->get();
        $baseUnits         = Product::BASE_UNITS;
        return view('products.edit', compact('product', 'productCategories', 'baseUnits'));
    }

    /**
     * Update produk (stok TIDAK bisa diubah dari sini)
     */
    public function update(Request $request, Product $product)
    {
        $validCategories = Category::product()->pluck('name')->toArray();

        $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'category'          => ['required', Rule::in($validCategories)],
            'base_unit'         => ['required', Rule::in(Product::BASE_UNITS)],
            'items_per_package' => ['required', 'integer', 'min:1'],
            'items_per_bundle'  => ['nullable', 'integer', 'min:1'],
            'minimum_stock'     => ['required', 'integer', 'min:0'],
            'purchase_price'    => ['required', 'numeric', 'min:0'],
            'selling_price'     => ['required', 'numeric', 'min:0'],
        ], [
            'name.required'              => 'Nama produk wajib diisi.',
            'category.required'          => 'Kategori wajib dipilih.',
            'category.in'                => 'Kategori tidak valid.',
            'base_unit.required'         => 'Satuan dasar wajib dipilih.',
            'base_unit.in'               => 'Satuan dasar tidak valid.',
            'items_per_package.required' => 'Jumlah per package wajib diisi.',
            'items_per_package.min'      => 'Jumlah per package minimal 1.',
            'minimum_stock.required'     => 'Stok minimum wajib diisi.',
            'purchase_price.required'    => 'Harga beli wajib diisi.',
            'selling_price.required'     => 'Harga jual wajib diisi.',
        ]);

        // Stok TIDAK ikut diupdate
        $product->update([
            'name'              => $request->name,
            'category'          => $request->category,
            'base_unit'         => $request->base_unit,
            'items_per_package' => $request->items_per_package,
            'items_per_bundle'  => $request->items_per_bundle ?? 1,
            'minimum_stock'     => $request->minimum_stock,
            'purchase_price'    => $request->purchase_price,
            'selling_price'     => $request->selling_price,
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Produk "' . $request->name . '" berhasil diperbarui.');
    }

    /**
     * Hapus produk
     */
        public function destroy(Product $product)
    {
        // Tidak bisa hapus jika ada riwayat transaksi
        if ($product->hasTransactionHistory()) {
            return back()->with('error', 'Produk "' . $product->name . '" tidak bisa dihapus karena memiliki riwayat transaksi.');
        }

        $name = $product->name;

        // Hapus stock_logs dulu (stok awal) sebelum hapus produk
        $product->stockLogs()->delete();
        $product->delete();

        return back()->with('success', 'Produk "' . $name . '" berhasil dihapus.');
    }
}