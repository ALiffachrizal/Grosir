<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'category',
    ];

    // ==================== RELASI ====================

    /**
     * Supplier bisa punya banyak purchase order
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    // ==================== HELPER ====================

    /**
     * Cek apakah supplier punya riwayat pemesanan
     * Digunakan sebelum hapus supplier
     */
    public function hasPurchaseOrders(): bool
    {
        return $this->purchaseOrders()->exists();
    }
}