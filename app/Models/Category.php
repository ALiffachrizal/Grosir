<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_kategori',
        'name',
        'type',
    ];

    // ==================== SCOPES ====================

    /**
     * Kategori bertipe 'product'.
     *
     * Sejak kategori supplier digabung ke kategori produk (lihat migration
     * 2026_07_14_000001_merge_supplier_categories_into_product), scope ini
     * sekarang dipakai BERSAMA oleh produk maupun supplier — bukan cuma
     * produk saja seperti sebelumnya.
     */
    public function scopeProduct($query)
    {
        return $query->where('type', 'product');
    }

    /**
     * Kategori bertipe 'unit' (satuan produk: PCS, BOTOL, LITER, dst).
     */
    public function scopeUnit($query)
    {
        return $query->where('type', 'unit');
    }

    // ==================== RELASI ====================

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }
}