<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DraftSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'note',
    ];

    // ==================== RELASI ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(DraftSaleDetail::class);
    }

    // ==================== ACCESSOR ====================

    public function getTotalEstimasiAttribute(): float
    {
        return $this->details->sum(
            fn (DraftSaleDetail $detail) => $detail->quantity * $detail->unit_price
        );
    }

    public function getTotalEstimasiFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->total_estimasi, 0, ',', '.');
    }

    public function getTotalItemAttribute(): int
    {
        return $this->details->sum('quantity');
    }
}