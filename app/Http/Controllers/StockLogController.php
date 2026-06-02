<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockLog;
use App\Models\Product;

class StockLogController extends Controller
{
    public function index(Request $request)
    {
        $query = StockLog::with(['product.category', 'user'])->latest();

        // Filter berdasarkan produk
        // Di form yang dikirim adalah product_id,
        // sedangkan tabel stock_logs menyimpan kode_produk.
        if ($request->filled('product_id')) {
            $product = Product::find($request->product_id);

            if ($product) {
                $query->where('kode_produk', $product->kode_produk);
            }
        }

        // Filter berdasarkan tipe perubahan stok
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter berdasarkan tanggal mulai
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // Filter berdasarkan tanggal akhir
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20)->withQueryString();

        $products = Product::with('category')
            ->orderBy('name')
            ->get();

        return view('stock-logs.index', compact('logs', 'products'));
    }
}