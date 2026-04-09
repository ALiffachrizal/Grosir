<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockLog;
use App\Models\Product;
use Carbon\Carbon;

class StockLogController extends Controller
{
    public function index(Request $request)
    {
        $query = StockLog::with(['product', 'user'])->latest();

        // Filter by produk
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by tipe
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by tanggal mulai
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // Filter by tanggal akhir
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs     = $query->paginate(20)->withQueryString();
        $products = Product::orderBy('name')->get();

        return view('stock-logs.index', compact('logs', 'products'));
    }
}