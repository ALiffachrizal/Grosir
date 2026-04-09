<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'total_price',
        'payment_method',
    ];

    protected function casts(): array
    {
        return [
            'date'        => 'date',
            'total_price' => 'decimal:2',
        ];
    }

    // ==================== RELASI ====================

    /**
     * Penjualan dilakukan oleh seorang user/kasir
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Penjualan punya banyak detail produk
     */
    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    /**
     * Penjualan bisa punya banyak refund
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    // ==================== ACCESSOR ====================

    /**
     * Label metode pembayaran
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'cash'     => 'Tunai',
            'transfer' => 'Transfer',
            default    => ucfirst($this->payment_method),
        };
    }

    /**
     * Format total harga ke Rupiah
     */
    public function getTotalPriceFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }
}