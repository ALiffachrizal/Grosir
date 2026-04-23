<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Primary key tetap id (auto increment)
    // kode_kategori hanya sebagai kode unik
    protected $fillable = [
        'kode_kategori',
        'name',
        'type',
    ];

    public function scopeProduct($query)
    {
        return $query->where('type', 'product');
    }

    public function scopeSupplier($query)
    {
        return $query->where('type', 'supplier');
    }
}