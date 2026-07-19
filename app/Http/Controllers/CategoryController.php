<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Menampilkan kategori (dipakai bersama produk & supplier) dan satuan.
     *
     * Sejak kategori supplier digabung ke kategori produk, halaman ini
     * tidak lagi menampilkan "Kategori Produk" dan "Kategori Supplier"
     * sebagai 2 daftar terpisah — sekarang cukup 1 daftar kategori yang
     * dipakai bersama oleh keduanya.
     */
    public function index()
    {
        $categories = Category::product()
            ->orderBy('name')
            ->get();

        $unitCategories = Category::unit()
            ->orderBy('name')
            ->get();

        return view('categories.index', compact(
            'categories',
            'unitCategories'
        ));
    }

    /**
     * Menambahkan kategori (dipakai produk & supplier) atau satuan tambahan.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_kategori' => [
                'required',
                'string',
                'max:10',
                'unique:categories,kode_kategori',
                'regex:/^[A-Za-z0-9]+$/',
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'type' => [
                'required',
                'in:product,unit',
            ],
        ], [
            'kode_kategori.required' => 'Kode kategori wajib diisi.',
            'kode_kategori.string' => 'Kode kategori harus berupa teks.',
            'kode_kategori.unique' => 'Kode kategori sudah digunakan.',
            'kode_kategori.max' => 'Kode kategori maksimal 10 karakter.',
            'kode_kategori.regex' => 'Kode hanya boleh berisi huruf dan angka.',

            'name.required' => 'Nama kategori wajib diisi.',
            'name.string' => 'Nama kategori harus berupa teks.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',

            'type.required' => 'Tipe kategori wajib dipilih.',
            'type.in' => 'Tipe kategori tidak valid.',
        ]);

        $kodeKategori = strtoupper(trim($request->kode_kategori));
        $name = strtoupper(trim($request->name));
        $type = $request->type;

        /*
        |--------------------------------------------------------------------------
        | Cegah satuan bawaan ditambahkan sebagai satuan tambahan
        |--------------------------------------------------------------------------
        */
        if (
            $type === 'unit'
            && in_array($name, Product::BASE_UNITS_DEFAULT, true)
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Satuan "' . $name . '" sudah tersedia sebagai satuan bawaan.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Cek duplikat nama berdasarkan tipe
        |--------------------------------------------------------------------------
        */
        $categoryExists = Category::where('type', $type)
            ->whereRaw('UPPER(name) = ?', [$name])
            ->exists();

        if ($categoryExists) {
            $label = $this->getTypeLabel($type);

            return back()
                ->withInput()
                ->with(
                    'error',
                    ucfirst($label) . ' "' . $name . '" sudah ada.'
                );
        }

        Category::create([
            'kode_kategori' => $kodeKategori,
            'name' => $name,
            'type' => $type,
        ]);

        $label = $this->getTypeLabel($type);

        return back()->with(
            'success',
            ucfirst($label) . ' "' . $name . '" berhasil ditambahkan.'
        );
    }

    /**
     * Menghapus kategori atau satuan tambahan.
     */
    public function destroy(Category $category)
    {
        /*
        |--------------------------------------------------------------------------
        | Kategori (dipakai produk & supplier)
        |--------------------------------------------------------------------------
        |
        | Karena sekarang 1 kategori bisa dipakai bersama oleh produk
        | MAUPUN supplier, kedua relasi ini harus dicek sebelum kategori
        | boleh dihapus — bukan cuma salah satu seperti sebelumnya.
        */
        if ($category->type === 'product') {
            $jumlahProduk = $category->products()->count();
            $jumlahSupplier = $category->suppliers()->count();

            if ($jumlahProduk > 0 || $jumlahSupplier > 0) {
                $rincian = [];

                if ($jumlahProduk > 0) {
                    $rincian[] = $jumlahProduk . ' produk';
                }

                if ($jumlahSupplier > 0) {
                    $rincian[] = $jumlahSupplier . ' supplier';
                }

                return back()->with(
                    'error',
                    'Kategori "' . $category->name .
                    '" tidak dapat dihapus karena masih digunakan oleh ' .
                    implode(' dan ', $rincian) . '.'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Satuan tambahan
        |--------------------------------------------------------------------------
        */
        if ($category->type === 'unit') {
            $jumlahProduk = Product::whereRaw(
                'UPPER(base_unit) = ?',
                [strtoupper($category->name)]
            )->count();

            if ($jumlahProduk > 0) {
                return back()->with(
                    'error',
                    'Satuan "' . $category->name .
                    '" tidak dapat dihapus karena masih digunakan oleh ' .
                    $jumlahProduk . ' produk.'
                );
            }
        }

        $name = $category->name;
        $label = $this->getTypeLabel($category->type);

        try {
            $category->delete();
        } catch (QueryException $exception) {
            report($exception);

            return back()->with(
                'error',
                ucfirst($label) . ' "' . $name .
                '" tidak dapat dihapus karena masih digunakan oleh data lain.'
            );
        }

        return back()->with(
            'success',
            ucfirst($label) . ' "' . $name . '" berhasil dihapus.'
        );
    }

    private function getTypeLabel(string $type): string
    {
        return match ($type) {
            'product' => 'kategori',
            'unit' => 'satuan',
            default => 'kategori',
        };
    }
}