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

    /**
     * Awalan (prefix) untuk kode produk yang di-generate otomatis.
     * Contoh hasil: PRD000001, PRD000002, dst.
     *
     * Panjang maksimal kolom kode_produk di database adalah 10 karakter
     * (lihat migration create_products_table). Prefix + 6 digit angka
     * menghasilkan 9 karakter, masih aman di bawah batas 10.
     */
    private const KODE_PRODUK_PREFIX = 'PRD';

    private const KODE_PRODUK_DIGIT_LENGTH = 6;

    /**
     * Hook otomatis dari Eloquent yang berjalan setiap kali model di-boot.
     * Di sinilah "otomatis di-generate oleh model" yang disebut di komentar
     * ProductController::store() akhirnya benar-benar diimplementasikan.
     */
    protected static function boot()
    {
        parent::boot();

        /*
        |--------------------------------------------------------------------------
        | Auto-generate kode_produk sebelum data disimpan
        |--------------------------------------------------------------------------
        |
        | Event 'creating' berjalan SEBELUM insert ke database, sehingga
        | kode_produk sudah terisi saat baris benar-benar disimpan — beda
        | dengan event 'created' yang berjalan SETELAH insert (yang mana
        | sudah terlambat untuk kolom NOT NULL seperti pada stock_logs).
        |
        | Kode hanya di-generate jika belum diisi manual. Ini menjaga
        | kompatibilitas jika suatu saat ada form yang mengizinkan admin
        | mengisi kode_produk sendiri.
        */
        static::creating(function (Product $product) {
            if (empty($product->kode_produk)) {
                $product->kode_produk = self::generateKodeProduk();
            }
        });
    }

    /**
     * Generate kode produk baru secara berurutan berdasarkan angka
     * terbesar yang sedang dipakai oleh kode berprefix sama.
     *
     * Tiga lapis perlindungan di sini:
     *
     * 1. Hanya kode yang FORMATNYA BENAR-BENAR BERSIH (prefix diikuti
     *    angka murni, contoh: PRD000014) yang dihitung. Kode lama yang
     *    formatnya tidak konsisten (misal sisa input manual seperti
     *    "PRD-01" atau kode dengan karakter lain) diabaikan dari
     *    perhitungan — supaya tidak membuat MySQL salah hitung saat
     *    CAST ke angka (kode dengan tanda minus, misalnya, bisa membuat
     *    hasil CAST meledak jadi angka raksasa akibat perilaku "wrap
     *    around" MySQL untuk angka unsigned negatif).
     *
     * 2. MAX() dihitung dari NILAI ANGKA-nya (bukan urutan string),
     *    supaya tidak salah hitung kalau ada kode dengan panjang digit
     *    yang berbeda.
     *
     * 3. Sebelum kode dikembalikan, dicek dulu apakah sudah dipakai.
     *    Kalau ternyata sudah ada, otomatis loncat ke nomor berikutnya
     *    sampai ketemu yang benar-benar kosong.
     */
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

        // Jaring pengaman: kalau kode hasil hitungan ternyata sudah
        // dipakai (data lama tidak konsisten / gap), terus naik sampai
        // ketemu kode yang benar-benar belum dipakai.
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