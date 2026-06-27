<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    /**
     * Menampilkan daftar supplier.
     */
    public function index(Request $request)
    {
        $query = Supplier::with('category')
            ->orderBy('name');

        if ($request->filled('category_id')) {
            $query->where(
                'category_id',
                $request->category_id
            );
        }

        $suppliers = $query->get();

        $supplierCategories = Category::supplier()
            ->orderBy('name')
            ->get();

        return view('suppliers.index', compact(
            'suppliers',
            'supplierCategories'
        ));
    }

    /**
     * Menampilkan form tambah supplier.
     */
    public function create()
    {
        $supplierCategories = Category::supplier()
            ->orderBy('name')
            ->get();

        return view('suppliers.create', compact(
            'supplierCategories'
        ));
    }

    /**
     * Menyimpan supplier baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_supplier' => [
                'required',
                'string',
                'max:10',
                'unique:suppliers,kode_supplier',
                'regex:/^[A-Za-z0-9]+$/',
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
                Rule::exists('categories', 'id')
                    ->where(function ($query) {
                        return $query->where(
                            'type',
                            'supplier'
                        );
                    }),
            ],
        ], [
            'kode_supplier.required' =>
                'Kode supplier wajib diisi.',

            'kode_supplier.string' =>
                'Kode supplier harus berupa teks.',

            'kode_supplier.unique' =>
                'Kode supplier sudah digunakan.',

            'kode_supplier.max' =>
                'Kode supplier maksimal 10 karakter.',

            'kode_supplier.regex' =>
                'Kode supplier hanya boleh berisi huruf dan angka.',

            'name.required' =>
                'Nama supplier wajib diisi.',

            'name.string' =>
                'Nama supplier harus berupa teks.',

            'name.max' =>
                'Nama supplier maksimal 255 karakter.',

            'phone.string' =>
                'Nomor telepon harus berupa teks.',

            'phone.max' =>
                'Nomor telepon maksimal 255 karakter.',

            'category_id.required' =>
                'Kategori supplier wajib dipilih.',

            'category_id.exists' =>
                'Kategori supplier tidak valid.',
        ]);

        $kodeSupplier = strtoupper(
            trim($validated['kode_supplier'])
        );

        $name = trim($validated['name']);

        $phone = isset($validated['phone'])
            ? trim($validated['phone'])
            : null;

        Supplier::create([
            'kode_supplier' => $kodeSupplier,
            'name' => $name,
            'phone' => $phone !== '' ? $phone : null,
            'category_id' => $validated['category_id'],
        ]);

        return redirect()
            ->route('suppliers.index')
            ->with(
                'success',
                'Supplier "' . $name . '" berhasil ditambahkan.'
            );
    }

    /**
     * Menampilkan detail supplier.
     */
    public function show(Supplier $supplier)
    {
        $supplier->load([
            'category',
            'purchaseOrders' => function ($query) {
                $query->latest()->take(10);
            },
        ]);

        $existingCodes = Supplier::orderBy('kode_supplier')
            ->pluck('kode_supplier', 'id');

        return view('suppliers.show', compact(
            'supplier',
            'existingCodes'
        ));
    }

    /**
     * Menampilkan form edit supplier.
     */
    public function edit(Supplier $supplier)
    {
        $supplierCategories = Category::supplier()
            ->orderBy('name')
            ->get();

        return view('suppliers.edit', compact(
            'supplier',
            'supplierCategories'
        ));
    }

    /**
     * Memperbarui data supplier.
     */
    public function update(
        Request $request,
        Supplier $supplier
    ) {
        $validated = $request->validate([
            'kode_supplier' => [
                'required',
                'string',
                'max:10',
                'regex:/^[A-Za-z0-9]+$/',

                Rule::unique(
                    'suppliers',
                    'kode_supplier'
                )->ignore($supplier->id),
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

                Rule::exists('categories', 'id')
                    ->where(function ($query) {
                        return $query->where(
                            'type',
                            'supplier'
                        );
                    }),
            ],
        ], [
            'kode_supplier.required' =>
                'Kode supplier wajib diisi.',

            'kode_supplier.string' =>
                'Kode supplier harus berupa teks.',

            'kode_supplier.unique' =>
                'Kode supplier sudah digunakan.',

            'kode_supplier.max' =>
                'Kode supplier maksimal 10 karakter.',

            'kode_supplier.regex' =>
                'Kode supplier hanya boleh berisi huruf dan angka.',

            'name.required' =>
                'Nama supplier wajib diisi.',

            'name.string' =>
                'Nama supplier harus berupa teks.',

            'name.max' =>
                'Nama supplier maksimal 255 karakter.',

            'phone.string' =>
                'Nomor telepon harus berupa teks.',

            'phone.max' =>
                'Nomor telepon maksimal 255 karakter.',

            'category_id.required' =>
                'Kategori supplier wajib dipilih.',

            'category_id.exists' =>
                'Kategori supplier tidak valid.',
        ]);

        $oldKodeSupplier = strtoupper(
            $supplier->kode_supplier
        );

        $newKodeSupplier = strtoupper(
            trim($validated['kode_supplier'])
        );

        $name = trim($validated['name']);

        $phone = isset($validated['phone'])
            ? trim($validated['phone'])
            : null;

        /*
        |--------------------------------------------------------------------------
        | Lindungi kode supplier yang sudah digunakan
        |--------------------------------------------------------------------------
        |
        | Purchase order menghubungkan supplier melalui kode_supplier.
        | Foreign key tersebut tidak menggunakan cascadeOnUpdate().
        |
        | Karena itu kode supplier tidak boleh diubah setelah supplier
        | memiliki riwayat purchase order.
        |
        */
        if (
            $newKodeSupplier !== $oldKodeSupplier
            && $supplier->hasPurchaseOrders()
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Kode supplier "' .
                    $supplier->kode_supplier .
                    '" tidak dapat diubah karena supplier sudah memiliki riwayat purchase order.'
                );
        }

        try {
            $supplier->update([
                'kode_supplier' => $newKodeSupplier,
                'name' => $name,
                'phone' => $phone !== '' ? $phone : null,
                'category_id' => $validated['category_id'],
            ]);
        } catch (QueryException $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Supplier gagal diperbarui karena kode supplier masih digunakan oleh data purchase order.'
                );
        }

        return redirect()
            ->route('suppliers.index')
            ->with(
                'success',
                'Supplier "' . $name . '" berhasil diperbarui.'
            );
    }

    /**
     * Menghapus supplier.
     */
    public function destroy(Supplier $supplier)
    {
        /*
        |--------------------------------------------------------------------------
        | Supplier dengan riwayat PO tidak boleh dihapus
        |--------------------------------------------------------------------------
        */
        if ($supplier->hasPurchaseOrders()) {
            return back()->with(
                'error',
                'Supplier "' .
                $supplier->name .
                '" tidak bisa dihapus karena memiliki riwayat purchase order.'
            );
        }

        $name = $supplier->name;

        try {
            $supplier->delete();
        } catch (QueryException $exception) {
            report($exception);

            return back()->with(
                'error',
                'Supplier "' .
                $name .
                '" tidak dapat dihapus karena masih digunakan oleh data lain.'
            );
        }

        return redirect()
            ->route('suppliers.index')
            ->with(
                'success',
                'Supplier "' .
                $name .
                '" berhasil dihapus.'
            );
    }
}