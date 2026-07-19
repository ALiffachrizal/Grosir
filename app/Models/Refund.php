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
        'kode_produk',
        'user_id',
        'quantity',
        'unit_price',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'date'       => 'date',
            'unit_price' => 'decimal:2',
        ];
    }

    // ==================== RELASI ====================

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'kode_produk', 'kode_produk');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== ACCESSOR ====================

    public function getUnitPriceFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->unit_price, 0, ',', '.');
    }

    /**
     * Nominal total refund ini (quantity x unit_price).
     */
    public function getTotalNominalAttribute(): float
    {
        return (float) $this->quantity * (float) $this->unit_price;
    }

    public function getTotalNominalFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->total_nominal, 0, ',', '.');
    }
}