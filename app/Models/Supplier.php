<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    // Primary key tetap id (auto increment)
    // kode_supplier hanya sebagai kode unik
    protected $fillable = [
        'kode_supplier',
        'name',
        'phone',
        'category',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'kode_supplier', 'kode_supplier');
    }

    public function hasPurchaseOrders(): bool
    {
        return $this->purchaseOrders()->exists();
    }
}