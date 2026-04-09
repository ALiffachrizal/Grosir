<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
    ];

    // ==================== SCOPE ====================

    /**
     * Hanya kategori produk
     */
    public function scopeProduct($query)
    {
        return $query->where('type', 'product');
    }

    /**
     * Hanya kategori supplier
     */
    public function scopeSupplier($query)
    {
        return $query->where('type', 'supplier');
    }
}