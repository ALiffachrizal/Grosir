<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'kode_produk',
        'quantity',
        'unit_price',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'kode_produk', 'kode_produk');
    }

    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    public function getSubtotalFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    public function getUnitPriceFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->unit_price, 0, ',', '.');
    }
}