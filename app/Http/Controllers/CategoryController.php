<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $productCategories  = Category::product()->orderBy('name')->get();
        $supplierCategories = Category::supplier()->orderBy('name')->get();
        $unitCategories     = Category::where('type', 'unit')->orderBy('name')->get();

        return view('categories.index', compact(
            'productCategories',
            'supplierCategories',
            'unitCategories'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_kategori' => [
                'required',
                'string',
                'max:10',
                'unique:categories,kode_kategori',
                'regex:/^[A-Za-z0-9]+$/'
            ],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:product,supplier,unit'],
        ], [
            'kode_kategori.required' => 'Kode kategori wajib diisi.',
            'kode_kategori.unique'   => 'Kode kategori sudah digunakan.',
            'kode_kategori.max'      => 'Kode kategori maksimal 10 karakter.',
            'kode_kategori.regex'    => 'Kode hanya boleh huruf dan angka.',
            'name.required'          => 'Nama kategori wajib diisi.',
            'type.required'          => 'Tipe wajib dipilih.',
        ]);

        // Cek duplikat nama + tipe
        $exists = Category::where('name', $request->name)
            ->where('type', $request->type)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', 'Kategori "' . $request->name . '" sudah ada.');
        }

        Category::create([
            'kode_kategori' => strtoupper($request->kode_kategori),
            'name'          => strtoupper($request->name),
            'type'          => $request->type,
        ]);

        $tipe = match($request->type) {
            'product'  => 'produk',
            'supplier' => 'supplier',
            'unit'     => 'satuan',
            default    => $request->type,
        };

        return back()->with('success', 'Kategori ' . $tipe . ' "' . $request->name . '" berhasil ditambahkan.');
    }

    public function destroy(Category $category)
    {
        $name = $category->name;
        $category->delete();
        return back()->with('success', 'Kategori "' . $name . '" berhasil dihapus.');
    }
}