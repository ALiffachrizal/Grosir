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

    private const KODE_PRODUK_PREFIX = 'PRD';

    private const KODE_PRODUK_DIGIT_LENGTH = 6;

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Product $product) {
            if (empty($product->kode_produk)) {
                $product->kode_produk = self::generateKodeProduk();
            }
        });
    }


    public static function generateKodeProduk(): string
    {
        $prefix = self::KODE_PRODUK_PREFIX;
        $prefixLength = strlen($prefix);

        $maxNumber = static::where('kode_produk', 'regexp', '^' . $prefix . '[0-9]+$')
            ->selectRaw(
                'MAX(CAST(SUBSTRING(kode_produk, ' . ($prefixLength + 1) . ') AS UNSIGNED)) as max_number'
            )
            ->value('max_number');

        $nextNumber = ((int) $maxNumber) + 1;

        $kode = $prefix . str_pad(
            (string) $nextNumber,
            self::KODE_PRODUK_DIGIT_LENGTH,
            '0',
            STR_PAD_LEFT
        );

        while (static::where('kode_produk', $kode)->exists()) {
            $nextNumber++;

            $kode = $prefix . str_pad(
                (string) $nextNumber,
                self::KODE_PRODUK_DIGIT_LENGTH,
                '0',
                STR_PAD_LEFT
            );
        }

        return $kode;
    }

    public static function getBaseUnits(): array
    {
        $fromDb = Category::unit()
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