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

    public function scopeProduct($query)
    {
        return $query->where('type', 'product');
    }

    public function scopeSupplier($query)
    {
        return $query->where('type', 'supplier');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }
}