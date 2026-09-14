<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DraftSaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'draft_sale_id',
        'kode_produk',
        'quantity',
        'bonus_quantity',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'bonus_quantity' => 'integer',
            'unit_price'     => 'decimal:2',
        ];
    }

    // ==================== RELASI ====================

    public function draftSale(): BelongsTo
    {
        return $this->belongsTo(DraftSale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'kode_produk', 'kode_produk');
    }

    // ==================== ACCESSOR ====================

    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    public function getTotalQuantityAttribute(): int
    {
        return (int) $this->quantity + (int) $this->bonus_quantity;
    }
}