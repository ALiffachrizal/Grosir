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
        $query = Supplier::with('category')->orderBy('name');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $suppliers = $query->get();
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
        $request->validate([
            'kode_supplier' => [
                'required',
                'string',
                'max:10',
                'unique:suppliers,kode_supplier',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:255',
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where('type', 'supplier');
                }),
            ],
        ], [
            'kode_supplier.required' => 'Kode supplier wajib diisi.',
            'kode_supplier.unique' => 'Kode supplier sudah digunakan.',
            'kode_supplier.max' => 'Kode supplier maksimal 10 karakter.',
            'name.required' => 'Nama supplier wajib diisi.',
            'phone.max' => 'Nomor telepon maksimal 255 karakter.',
            'category_id.required' => 'Kategori supplier wajib dipilih.',
            'category_id.exists' => 'Kategori supplier tidak valid.',
        ]);

        Supplier::create([
            'kode_supplier' => strtoupper($request->kode_supplier),
            'name' => $request->name,
            'phone' => $request->phone,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier "' . $request->name . '" berhasil ditambahkan.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load([
            'category',
            'purchaseOrders' => function ($query) {
                $query->latest()->take(10);
            },
        ]);

        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        $supplierCategories = Category::supplier()->orderBy('name')->get();

        return view('suppliers.edit', compact('supplier', 'supplierCategories'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'kode_supplier' => [
                'required',
                'string',
                'max:10',
                Rule::unique('suppliers', 'kode_supplier')->ignore($supplier->id),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:255',
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where('type', 'supplier');
                }),
            ],
        ], [
            'kode_supplier.required' => 'Kode supplier wajib diisi.',
            'kode_supplier.unique' => 'Kode supplier sudah digunakan.',
            'kode_supplier.max' => 'Kode supplier maksimal 10 karakter.',
            'name.required' => 'Nama supplier wajib diisi.',
            'phone.max' => 'Nomor telepon maksimal 255 karakter.',
            'category_id.required' => 'Kategori supplier wajib dipilih.',
            'category_id.exists' => 'Kategori supplier tidak valid.',
        ]);

        $supplier->update([
            'kode_supplier' => strtoupper($request->kode_supplier),
            'name' => $request->name,
            'phone' => $request->phone,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier "' . $request->name . '" berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->hasPurchaseOrders()) {
            return back()->with('error',
                'Supplier "' . $supplier->name . '" tidak bisa dihapus karena memiliki riwayat purchase order.'
            );
        }

        $name = $supplier->name;
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier "' . $name . '" berhasil dihapus.');
    }
}