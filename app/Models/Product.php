<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    // Primary key tetap id (auto increment)
    // kode_produk hanya sebagai kode unik
    protected $fillable = [
        'kode_produk',
        'name',
        'category',
        'base_unit',
        'items_per_package',
        'items_per_bundle',
        'stock',
        'minimum_stock',
        'purchase_price',
        'selling_price',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price'  => 'decimal:2',
        ];
    }

    const BASE_UNITS_DEFAULT = ['PCS', 'BOTOL', 'LITER', 'KG'];

    public static function getBaseUnits(): array
    {
        $fromDb = \App\Models\Category::where('type', 'unit')
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        // Gabungkan bawaan + dari DB, hilangkan duplikat
        return array_unique(array_merge(self::BASE_UNITS_DEFAULT, $fromDb));
    }

    public function purchaseOrderDetails(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class, 'kode_produk', 'kode_produk');
    }

    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class, 'kode_produk', 'kode_produk');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'kode_produk', 'kode_produk');
    }

    public function stockLogs(): HasMany
    {
        return $this->hasMany(StockLog::class, 'kode_produk', 'kode_produk');
    }

    public function getStokMenipisAttribute(): bool
    {
        return $this->stock <= $this->minimum_stock;
    }

    public function getPackageLabelAttribute(): string
    {
        return $this->base_unit === 'KG' ? 'Karung' : 'Package';
    }

    public function getSellingPriceFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->selling_price, 0, ',', '.');
    }

    public function getPurchasePriceFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->purchase_price, 0, ',', '.');
    }

    public function hasTransactionHistory(): bool
    {
        return $this->purchaseOrderDetails()->exists()
            || $this->saleDetails()->exists()
            || $this->refunds()->exists();
    }
}