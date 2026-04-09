<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Supplier;
use App\Models\Category;

class SupplierController extends Controller
{
    /**
     * Daftar semua supplier
     */
    public function index(Request $request)
    {
        $query = Supplier::orderBy('name');

        // Filter berdasarkan kategori
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $suppliers           = $query->get();
        $supplierCategories  = Category::supplier()->orderBy('name')->get();

        return view('suppliers.index', compact('suppliers', 'supplierCategories'));
    }

    /**
     * Form tambah supplier
     */
    public function create()
    {
        $supplierCategories = Category::supplier()->orderBy('name')->get();
        return view('suppliers.create', compact('supplierCategories'));
    }

    /**
     * Simpan supplier baru
     */
    public function store(Request $request)
    {
        // Ambil daftar kategori supplier dari DB untuk validasi
        $validCategories = Category::supplier()->pluck('name')->toArray();

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'category' => ['required', Rule::in($validCategories)],
        ], [
            'name.required'     => 'Nama supplier wajib diisi.',
            'category.required' => 'Kategori wajib dipilih.',
            'category.in'       => 'Kategori tidak valid.',
        ]);

        Supplier::create([
            'name'     => $request->name,
            'phone'    => $request->phone,
            'category' => $request->category,
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier "' . $request->name . '" berhasil ditambahkan.');
    }

    /**
     * Detail supplier
     */
    public function show(Supplier $supplier)
    {
        $supplier->loadCount('purchaseOrders');
        return view('suppliers.show', compact('supplier'));
    }

    /**
     * Form edit supplier
     */
    public function edit(Supplier $supplier)
    {
        $supplierCategories = Category::supplier()->orderBy('name')->get();
        return view('suppliers.edit', compact('supplier', 'supplierCategories'));
    }

    /**
     * Update supplier
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validCategories = Category::supplier()->pluck('name')->toArray();

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'category' => ['required', Rule::in($validCategories)],
        ], [
            'name.required'     => 'Nama supplier wajib diisi.',
            'category.required' => 'Kategori wajib dipilih.',
            'category.in'       => 'Kategori tidak valid.',
        ]);

        $supplier->update([
            'name'     => $request->name,
            'phone'    => $request->phone,
            'category' => $request->category,
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier "' . $request->name . '" berhasil diperbarui.');
    }

    /**
     * Hapus supplier
     */
    public function destroy(Supplier $supplier)
    {
        // Tidak bisa hapus jika ada riwayat pemesanan
        if ($supplier->hasPurchaseOrders()) {
            return back()->with('error', 'Supplier "' . $supplier->name . '" tidak bisa dihapus karena memiliki riwayat pemesanan.');
        }

        $name = $supplier->name;
        $supplier->delete();

        return back()->with('success', 'Supplier "' . $name . '" berhasil dihapus.');
    }
}