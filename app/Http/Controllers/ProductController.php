<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Product;
use App\Models\Category;
use App\Models\StockLog;

class ProductController extends Controller
{
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

    public function create()
    {
        $productCategories = Category::product()->orderBy('name')->get();
        $baseUnits         = Product::getBaseUnits(); // ambil dari DB
        return view('products.create', compact('productCategories', 'baseUnits'));
    }   

    public function store(Request $request)
    {
        $validCategories = Category::product()->pluck('name')->toArray();

        $request->validate([
            'kode_produk'       => ['required', 'string', 'max:10', 'unique:products,kode_produk'],
            'name'              => ['required', 'string', 'max:255'],
            'category'          => ['required', Rule::in($validCategories)],
            'base_unit' => ['required', Rule::in(Product::getBaseUnits())],
            'items_per_package' => ['required', 'integer', 'min:1'],
            'items_per_bundle'  => ['nullable', 'integer', 'min:1'],
            'stock'             => ['nullable', 'integer', 'min:0'],
            'minimum_stock'     => ['required', 'integer', 'min:0'],
            'purchase_price'    => ['required', 'numeric', 'min:0'],
            'selling_price'     => ['required', 'numeric', 'min:0'],
        ], [
            'kode_produk.required' => 'Kode produk wajib diisi.',
            'kode_produk.unique'   => 'Kode produk sudah digunakan.',
            'kode_produk.max'      => 'Kode produk maksimal 10 karakter.',
            'name.required'        => 'Nama produk wajib diisi.',
            'category.required'    => 'Kategori wajib dipilih.',
            'category.in'          => 'Kategori tidak valid.',
            'base_unit.required'   => 'Satuan dasar wajib dipilih.',
            'base_unit.in'         => 'Satuan dasar tidak valid.',
            'items_per_package.required' => 'Jumlah per package wajib diisi.',
            'minimum_stock.required'     => 'Stok minimum wajib diisi.',
            'purchase_price.required'    => 'Harga beli wajib diisi.',
            'selling_price.required'     => 'Harga jual wajib diisi.',
        ]);

        $product = Product::create([
            'kode_produk'       => strtoupper($request->kode_produk),
            'name'              => $request->name,
            'category'          => strtoupper($request->category),
            'base_unit'         => $request->base_unit,
            'items_per_package' => $request->items_per_package,
            'items_per_bundle'  => $request->items_per_bundle ?? 1,
            'stock'             => $request->stock ?? 0,
            'minimum_stock'     => $request->minimum_stock,
            'purchase_price'    => $request->purchase_price,
            'selling_price'     => $request->selling_price,
        ]);

        // Catat stok awal
        if ($product->stock > 0) {
            StockLog::create([
                'kode_produk'    => $product->kode_produk,
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

    public function show(Product $product)
    {
        $product->load(['stockLogs' => function ($q) {
            $q->with('user')->latest()->take(10);
        }]);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $productCategories = Category::product()->orderBy('name')->get();
        $baseUnits         = Product::getBaseUnits(); // ambil dari DB
        return view('products.edit', compact('product', 'productCategories', 'baseUnits'));
    }

    public function update(Request $request, Product $product)
    {
        $validCategories = Category::product()->pluck('name')->toArray();

        $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'category'          => ['required', Rule::in($validCategories)],
            'base_unit' => ['required', Rule::in(Product::getBaseUnits())],
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
            'minimum_stock.required'     => 'Stok minimum wajib diisi.',
            'purchase_price.required'    => 'Harga beli wajib diisi.',
            'selling_price.required'     => 'Harga jual wajib diisi.',
        ]);

        // Kode produk TIDAK diubah saat edit
        // Stok TIDAK diubah dari sini
        $product->update([
            'name'              => $request->name,
            'category'          => strtoupper($request->category),
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

    public function destroy(Product $product)
    {
        if ($product->hasTransactionHistory()) {
            return back()->with('error',
                'Produk "' . $product->name . '" tidak bisa dihapus karena memiliki riwayat transaksi.'
            );
        }

        $name = $product->name;
        $product->stockLogs()->delete();
        $product->delete();

        return back()->with('success', 'Produk "' . $name . '" berhasil dihapus.');
    }
}