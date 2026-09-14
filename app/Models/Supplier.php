<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $primaryKey = 'kode_supplier';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_supplier',
        'name',
        'phone',
        'kode_kategori',
    ];

    protected $appends = [
        'category_name',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'kode_kategori', 'kode_kategori');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'kode_supplier', 'kode_supplier');
    }

    public function getCategoryNameAttribute(): string
    {
        return $this->category?->name ?? '-';
    }

    public function hasPurchaseOrders(): bool
    {
        return $this->purchaseOrders()->exists();
    }
}