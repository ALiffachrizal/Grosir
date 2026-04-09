<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'order_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
        ];
    }

    // ==================== RELASI ====================

    /**
     * Purchase order dibuat oleh seorang user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Purchase order ditujukan ke seorang supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Purchase order punya banyak detail produk
     */
    public function details(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class);
    }

    // ==================== ACCESSOR ====================

    /**
     * Label status dalam Bahasa Indonesia
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'  => 'Menunggu',
            'received' => 'Diterima',
            default    => ucfirst($this->status),
        };
    }

    /**
     * Warna badge status untuk Tailwind CSS
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'  => 'bg-yellow-100 text-yellow-700',
            'received' => 'bg-green-100 text-green-700',
            default    => 'bg-gray-100 text-gray-700',
        };
    }
}