<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
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

    // ==================== KONSTANTA ====================

    /**
     * Daftar base unit yang valid (hardcoded)
     */
    const BASE_UNITS = ['PCS', 'BOTOL', 'LITER', 'KG'];

    // ==================== RELASI ====================

    /**
     * Produk bisa ada di banyak detail purchase order
     */
    public function purchaseOrderDetails(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class);
    }

    /**
     * Produk bisa ada di banyak detail penjualan
     */
    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    /**
     * Produk bisa di-refund berkali-kali
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Produk tercatat di banyak stock log
     */
    public function stockLogs(): HasMany
    {
        return $this->hasMany(StockLog::class);
    }

    // ==================== ACCESSOR ====================

    /**
     * Cek apakah stok menipis (stock <= minimum_stock)
     * Digunakan untuk badge & notifikasi sidebar
     */
    public function getStokMenipisAttribute(): bool
    {
        return $this->stock <= $this->minimum_stock;
    }

    /**
     * Label nama satuan package
     * Jika base_unit = KG maka label = "Karung", selainnya = "Package"
     */
    public function getPackageLabelAttribute(): string
    {
        return $this->base_unit === 'KG' ? 'Karung' : 'Package';
    }

    /**
     * Format harga jual ke Rupiah
     */
    public function getSellingPriceFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->selling_price, 0, ',', '.');
    }

    /**
     * Format harga beli ke Rupiah
     */
    public function getPurchasePriceFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->purchase_price, 0, ',', '.');
    }

    // ==================== HELPER ====================

    /**
     * Cek apakah produk punya riwayat transaksi
     * Digunakan sebelum hapus produk
     */
    public function hasTransactionHistory(): bool
    {
        return $this->purchaseOrderDetails()->exists()
            || $this->saleDetails()->exists()
            || $this->refunds()->exists();
    }
}