<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'kode_produk',
        'quantity',
        'quantity_received',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'kode_produk', 'kode_produk');
    }

    
    public function getIsShortDeliveryAttribute(): bool
    {
        return $this->quantity_received !== null
            && $this->quantity_received < $this->quantity;
    }
}