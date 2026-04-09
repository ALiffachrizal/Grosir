<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Tampilkan semua kategori (produk & supplier)
     */
    public function index()
    {
        $productCategories  = Category::product()->orderBy('name')->get();
        $supplierCategories = Category::supplier()->orderBy('name')->get();

        return view('categories.index', compact('productCategories', 'supplierCategories'));
    }

    /**
     * Simpan kategori baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:product,supplier'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'type.required' => 'Tipe kategori wajib dipilih.',
        ]);

        // Cek duplikat nama + tipe
        $exists = Category::where('name', $request->name)
            ->where('type', $request->type)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'Kategori "' . $request->name . '" sudah ada.');
        }

        Category::create([
            'name' => $request->name,
            'type' => $request->type,
        ]);

        $tipe = $request->type === 'product' ? 'produk' : 'supplier';
        return back()->with('success', 'Kategori ' . $tipe . ' "' . $request->name . '" berhasil ditambahkan.');
    }

    /**
     * Hapus kategori
     */
    public function destroy(Category $category)
    {
        $name = $category->name;
        $category->delete();

        return back()->with('success', 'Kategori "' . $name . '" berhasil dihapus.');
    }
}