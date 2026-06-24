<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Menampilkan kategori produk, supplier, dan satuan tambahan.
     */
    public function index()
    {
        $productCategories = Category::product()
            ->orderBy('name')
            ->get();

        $supplierCategories = Category::supplier()
            ->orderBy('name')
            ->get();

        $unitCategories = Category::where('type', 'unit')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact(
            'productCategories',
            'supplierCategories',
            'unitCategories'
        ));
    }

    /**
     * Menambahkan kategori produk, kategori supplier,
     * atau satuan tambahan.
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
                'in:product,supplier,unit',
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

        /*
        |--------------------------------------------------------------------------
        | Normalisasi input
        |--------------------------------------------------------------------------
        |
        | Kode dan nama disimpan dalam huruf kapital agar data kategori
        | dan satuan tetap konsisten.
        |
        */
        $kodeKategori = strtoupper(trim($request->kode_kategori));
        $name = strtoupper(trim($request->name));
        $type = $request->type;

        /*
        |--------------------------------------------------------------------------
        | Cegah satuan bawaan ditambahkan sebagai satuan tambahan
        |--------------------------------------------------------------------------
        |
        | PCS, BOTOL, LITER, dan KG sudah tersedia dari Product.
        | Karena itu nama tersebut tidak boleh dibuat lagi pada database.
        |
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
        |
        | Nama yang sama masih boleh digunakan pada tipe berbeda.
        | Contoh: nama yang sama dapat digunakan untuk kategori produk
        | dan kategori supplier apabila memang diperlukan.
        |
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
        | Kategori produk
        |--------------------------------------------------------------------------
        |
        | Kategori produk tidak boleh dihapus jika masih digunakan
        | oleh satu atau lebih produk.
        |
        */
        if ($category->type === 'product' && $category->products()->exists()) {
            $jumlahProduk = $category->products()->count();

            return back()->with(
                'error',
                'Kategori produk "' . $category->name .
                '" tidak dapat dihapus karena masih digunakan oleh ' .
                $jumlahProduk . ' produk.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Kategori supplier
        |--------------------------------------------------------------------------
        |
        | Kategori supplier tidak boleh dihapus jika masih digunakan
        | oleh satu atau lebih supplier.
        |
        */
        if ($category->type === 'supplier' && $category->suppliers()->exists()) {
            $jumlahSupplier = $category->suppliers()->count();

            return back()->with(
                'error',
                'Kategori supplier "' . $category->name .
                '" tidak dapat dihapus karena masih digunakan oleh ' .
                $jumlahSupplier . ' supplier.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Satuan tambahan
        |--------------------------------------------------------------------------
        |
        | Satuan disimpan sebagai teks pada products.base_unit sehingga
        | tidak memiliki foreign key langsung ke tabel categories.
        | Karena itu pemeriksaannya dilakukan secara manual.
        |
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

        /*
        |--------------------------------------------------------------------------
        | Hapus data
        |--------------------------------------------------------------------------
        |
        | QueryException tetap ditangani untuk mencegah pengguna melihat
        | halaman error database jika terdapat relasi yang belum terdeteksi.
        |
        */
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

    /**
     * Mengubah nilai type menjadi nama yang mudah dibaca pengguna.
     */
    private function getTypeLabel(string $type): string
    {
        return match ($type) {
            'product' => 'kategori produk',
            'supplier' => 'kategori supplier',
            'unit' => 'satuan',
            default => 'kategori',
        };
    }
}