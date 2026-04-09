<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class StoreProfileController extends Controller
{
    public function index()
    {
        // Ambil kategori produk untuk ditampilkan
        $categories = Category::product()->orderBy('name')->get();

        // Statistik toko
        $totalProducts   = Product::count();
        $totalCategories = Category::product()->count();

        return view('store-profile', compact(
            'categories',
            'totalProducts',
            'totalCategories'
        ));
    }
}