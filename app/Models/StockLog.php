<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'note',
    ];

    // ==================== RELASI ====================

    /**
     * Log merujuk ke satu produk
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Log dicatat oleh seorang user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== ACCESSOR ====================

    /**
     * Label tipe log dalam Bahasa Indonesia
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'in'     => 'Masuk',
            'out'    => 'Keluar',
            'refund' => 'Refund',
            default  => ucfirst($this->type),
        };
    }

    /**
     * Warna badge tipe untuk Tailwind CSS
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'in'     => 'bg-green-100 text-green-700',
            'out'    => 'bg-red-100 text-red-700',
            'refund' => 'bg-yellow-100 text-yellow-700',
            default  => 'bg-gray-100 text-gray-700',
        };
    }

    /**
     * Label referensi transaksi
     */
    public function getReferenceLabelAttribute(): string
    {
        return match($this->reference_type) {
            'purchase_order' => 'PO #' . $this->reference_id,
            'sale'           => 'Penjualan #' . $this->reference_id,
            'refund'         => 'Refund #' . $this->reference_id,
            default          => ucfirst($this->reference_type) . ' #' . $this->reference_id,
        };
    }
}