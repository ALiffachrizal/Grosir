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
        $query = Product::with('category');

        if ($request->filled('kode_kategori')) {
            $query->where('kode_kategori', $request->kode_kategori);
        }

        if ($request->stock_status === 'menipis') {
            $query->whereColumn('stock', '<=', 'minimum_stock');
        }

        if ($request->stock_status === 'aman') {
            $query->whereColumn('stock', '>', 'minimum_stock');
        }

        $products = $query
            ->orderBy('name')
            ->get();

        $productCategories = Category::product()
            ->orderBy('name')
            ->get();

        return view('products.index', compact(
            'products',
            'productCategories'
        ));
    }

    public function create()
    {
        $productCategories = Category::product()->orderBy('name')->get();
        $baseUnits = Product::getBaseUnits();

        return view('products.create', compact('productCategories', 'baseUnits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'kode_kategori' => [
                'required',
                Rule::exists('categories', 'kode_kategori')->where(function ($query) {
                    return $query->where('type', 'product');
                }),
            ],
            'base_unit' => [
                'required',
                Rule::in(Product::getBaseUnits()),
            ],
            'items_per_package' => [
                'required',
                'integer',
                'min:1',
            ],
            'items_per_bundle' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'stock' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'minimum_stock' => [
                'required',
                'integer',
                'min:0',
            ],
            'purchase_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'selling_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'promo_min_qty' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'promo_bonus_qty' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ], [
            'name.required' => 'Nama produk wajib diisi.',

            'kode_kategori.required' => 'Kategori wajib dipilih.',
            'kode_kategori.exists'   => 'Kategori tidak valid.',

            'base_unit.required' => 'Satuan dasar wajib dipilih.',
            'base_unit.in'       => 'Satuan dasar tidak valid.',

            'items_per_package.required' => 'Jumlah per package wajib diisi.',
            'items_per_package.integer'  => 'Jumlah per package harus berupa angka.',
            'items_per_package.min'      => 'Jumlah per package minimal 1.',

            'items_per_bundle.integer' => 'Jumlah per bundle harus berupa angka.',
            'items_per_bundle.min'     => 'Jumlah per bundle minimal 1.',

            'stock.integer' => 'Stok harus berupa angka.',
            'stock.min'     => 'Stok tidak boleh kurang dari 0.',

            'minimum_stock.required' => 'Stok minimum wajib diisi.',
            'minimum_stock.integer'  => 'Stok minimum harus berupa angka.',
            'minimum_stock.min'      => 'Stok minimum tidak boleh kurang dari 0.',

            'purchase_price.required' => 'Harga beli wajib diisi.',
            'purchase_price.numeric'  => 'Harga beli harus berupa angka.',
            'purchase_price.min'      => 'Harga beli tidak boleh kurang dari 0.',

            'selling_price.required' => 'Harga jual wajib diisi.',
            'selling_price.numeric'  => 'Harga jual harus berupa angka.',
            'selling_price.min'      => 'Harga jual tidak boleh kurang dari 0.',

            'promo_min_qty.integer'   => 'Minimal beli promo harus berupa angka bulat.',
            'promo_min_qty.min'       => 'Minimal beli promo tidak boleh kurang dari 0.',
            'promo_bonus_qty.integer' => 'Bonus promo harus berupa angka bulat.',
            'promo_bonus_qty.min'     => 'Bonus promo tidak boleh kurang dari 0.',
        ]);

        // kode_produk tidak perlu diisi — otomatis di-generate oleh model
        $product = Product::create([
            'name'              => $request->name,
            'kode_kategori'     => $request->kode_kategori,
            'base_unit'         => $request->base_unit,
            'items_per_package' => $request->items_per_package,
            'items_per_bundle'  => $request->items_per_bundle ?? 1,
            'stock'             => $request->stock ?? 0,
            'minimum_stock'     => $request->minimum_stock,
            'purchase_price'    => $request->purchase_price,
            'selling_price'     => $request->selling_price,
            'promo_min_qty'     => $request->filled('promo_min_qty') ? (int) $request->promo_min_qty : 0,
            'promo_bonus_qty'   => $request->filled('promo_bonus_qty') ? (int) $request->promo_bonus_qty : 0,
        ]);

        if ($product->stock > 0) {
            StockLog::create([
                'kode_produk'    => $product->kode_produk,
                'username'       => auth()->user()->username,
                'type'           => 'in',
                'quantity'       => $product->stock,
                'reference_type' => 'initial_stock',
                'reference_id'   => null,
                'note'           => 'Stok awal produk',
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Produk "' . $request->name . '" berhasil ditambahkan dengan kode ' . $product->kode_produk . '.');
    }

    public function show(Product $product)
    {
        $product->load([
            'category',
            'stockLogs' => function ($query) {
                $query->with('user')->latest()->take(10);
            },
        ]);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $productCategories = Category::product()->orderBy('name')->get();
        $baseUnits = Product::getBaseUnits();

        return view('products.edit', compact('product', 'productCategories', 'baseUnits'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'kode_kategori' => [
                'required',
                Rule::exists('categories', 'kode_kategori')->where(function ($query) {
                    return $query->where('type', 'product');
                }),
            ],
            'base_unit' => [
                'required',
                Rule::in(Product::getBaseUnits()),
            ],
            'items_per_package' => [
                'required',
                'integer',
                'min:1',
            ],
            'items_per_bundle' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'minimum_stock' => [
                'required',
                'integer',
                'min:0',
            ],
            'purchase_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'selling_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'promo_min_qty' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'promo_bonus_qty' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ], [
            'name.required' => 'Nama produk wajib diisi.',

            'kode_kategori.required' => 'Kategori wajib dipilih.',
            'kode_kategori.exists'   => 'Kategori tidak valid.',

            'base_unit.required' => 'Satuan dasar wajib dipilih.',
            'base_unit.in'       => 'Satuan dasar tidak valid.',

            'items_per_package.required' => 'Jumlah per package wajib diisi.',
            'items_per_package.integer'  => 'Jumlah per package harus berupa angka.',
            'items_per_package.min'      => 'Jumlah per package minimal 1.',

            'items_per_bundle.integer' => 'Jumlah per bundle harus berupa angka.',
            'items_per_bundle.min'     => 'Jumlah per bundle minimal 1.',

            'minimum_stock.required' => 'Stok minimum wajib diisi.',
            'minimum_stock.integer'  => 'Stok minimum harus berupa angka.',
            'minimum_stock.min'      => 'Stok minimum tidak boleh kurang dari 0.',

            'purchase_price.required' => 'Harga beli wajib diisi.',
            'purchase_price.numeric'  => 'Harga beli harus berupa angka.',
            'purchase_price.min'      => 'Harga beli tidak boleh kurang dari 0.',

            'selling_price.required' => 'Harga jual wajib diisi.',
            'selling_price.numeric'  => 'Harga jual harus berupa angka.',
            'selling_price.min'      => 'Harga jual tidak boleh kurang dari 0.',

            'promo_min_qty.integer'   => 'Minimal beli promo harus berupa angka bulat.',
            'promo_min_qty.min'       => 'Minimal beli promo tidak boleh kurang dari 0.',
            'promo_bonus_qty.integer' => 'Bonus promo harus berupa angka bulat.',
            'promo_bonus_qty.min'     => 'Bonus promo tidak boleh kurang dari 0.',
        ]);

        $product->update([
            'name'              => $request->name,
            'kode_kategori'     => $request->kode_kategori,
            'base_unit'         => $request->base_unit,
            'items_per_package' => $request->items_per_package,
            'items_per_bundle'  => $request->items_per_bundle ?? 1,
            'minimum_stock'     => $request->minimum_stock,
            'purchase_price'    => $request->purchase_price,
            'selling_price'     => $request->selling_price,
            'promo_min_qty'     => $request->filled('promo_min_qty') ? (int) $request->promo_min_qty : 0,
            'promo_bonus_qty'   => $request->filled('promo_bonus_qty') ? (int) $request->promo_bonus_qty : 0,
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