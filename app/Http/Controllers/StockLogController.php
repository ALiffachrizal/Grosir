<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockLog;
use App\Models\Product;

class StockLogController extends Controller
{
    public function index(Request $request)
{
    $query = StockLog::with(['product', 'user'])->latest();

    // Cari berdasarkan nama atau kode produk
    if ($request->filled('product_search')) {
        $search = $request->product_search;

        $query->whereHas('product', function ($productQuery) use ($search) {
            $productQuery
                ->where('name', 'like', '%' . $search . '%')
                ->orWhere('kode_produk', 'like', '%' . $search . '%');
        });
    }

    // Filter tipe
    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }

    // Filter tanggal mulai
    if ($request->filled('date_from')) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }

    // Filter tanggal akhir
    if ($request->filled('date_to')) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }

    $logs = $query->paginate(20)->withQueryString();

    return view('stock-logs.index', compact('logs'));
}
}