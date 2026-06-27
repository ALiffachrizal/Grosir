<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_produk',
        'name',
        'category_id',
        'base_unit',
        'items_per_package',
        'items_per_bundle',
        'stock',
        'minimum_stock',
        'purchase_price',
        'selling_price',
    ];

    protected $appends = [
        'category_name',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price'  => 'decimal:2',
        ];
    }

    const BASE_UNITS_DEFAULT = ['PCS', 'BOTOL', 'LITER', 'KG'];

    // ==================== AUTO-GENERATE KODE ====================

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (!$product->kode_produk) {
                $last = static::whereNotNull('kode_produk')
                    ->where('kode_produk', 'like', 'PRD-%')
                    ->max('kode_produk');

                $number = $last ? ((int) substr($last, 4)) + 1 : 1;

                $product->kode_produk = 'PRD-' . str_pad($number, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // ==================== HELPER ====================

    public static function getBaseUnits(): array
    {
        $fromDb = Category::where('type', 'unit')
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        return array_unique(array_merge(self::BASE_UNITS_DEFAULT, $fromDb));
    }

    // ==================== RELASI ====================

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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

    // ==================== ACCESSOR ====================

    public function getCategoryNameAttribute(): string
    {
        return $this->category?->name ?? '-';
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