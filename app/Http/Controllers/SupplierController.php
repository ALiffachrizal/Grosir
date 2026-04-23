<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Supplier;
use App\Models\Category;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::orderBy('name');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $suppliers          = $query->get();
        $supplierCategories = Category::supplier()->orderBy('name')->get();

        return view('suppliers.index', compact('suppliers', 'supplierCategories'));
    }

    public function create()
    {
        $supplierCategories = Category::supplier()->orderBy('name')->get();
        return view('suppliers.create', compact('supplierCategories'));
    }

    public function store(Request $request)
    {
        $validCategories = Category::supplier()->pluck('name')->toArray();

        $request->validate([
            'kode_supplier' => ['required', 'string', 'max:10', 'unique:suppliers,kode_supplier'],
            'name'          => ['required', 'string', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'category'      => ['required', Rule::in($validCategories)],
        ], [
            'kode_supplier.required' => 'Kode supplier wajib diisi.',
            'kode_supplier.unique'   => 'Kode supplier sudah digunakan.',
            'kode_supplier.max'      => 'Kode supplier maksimal 10 karakter.',
            'name.required'          => 'Nama supplier wajib diisi.',
            'category.required'      => 'Kategori wajib dipilih.',
            'category.in'            => 'Kategori tidak valid.',
        ]);

        Supplier::create([
            'kode_supplier' => strtoupper($request->kode_supplier),
            'name'          => $request->name,
            'phone'         => $request->phone,
            'category'      => strtoupper($request->category),
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier "' . $request->name . '" berhasil ditambahkan.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->loadCount('purchaseOrders');
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        $supplierCategories = Category::supplier()->orderBy('name')->get();
        return view('suppliers.edit', compact('supplier', 'supplierCategories'));
    }

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

        // Kode supplier tidak diubah saat edit
        $supplier->update([
            'name'     => $request->name,
            'phone'    => $request->phone,
            'category' => strtoupper($request->category),
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier "' . $request->name . '" berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->hasPurchaseOrders()) {
            return back()->with('error',
                'Supplier "' . $supplier->name . '" tidak bisa dihapus karena memiliki riwayat pemesanan.'
            );
        }

        $name = $supplier->name;
        $supplier->delete();

        return back()->with('success', 'Supplier "' . $name . '" berhasil dihapus.');
    }
}