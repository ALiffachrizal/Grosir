<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'user_id',
        'quantity',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    // ==================== RELASI ====================

    /**
     * Refund merujuk ke transaksi penjualan asal
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Refund merujuk ke produk yang dikembalikan
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Refund diproses oleh seorang user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}