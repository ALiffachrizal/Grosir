<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'username';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'username',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // ==================== RELASI ====================

    /**
     * User bisa membuat banyak purchase order
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'username', 'username');
    }

    /**
     * User bisa melakukan banyak penjualan
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'username', 'username');
    }

    /**
     * User bisa memproses banyak refund
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'username', 'username');
    }

    /**
     * User tercatat di banyak stock log
     */
    public function stockLogs(): HasMany
    {
        return $this->hasMany(StockLog::class, 'username', 'username');
    }

    /**
     * User memiliki banyak draft penjualan
     */
    public function draftSales(): HasMany
    {
        return $this->hasMany(DraftSale::class, 'username', 'username');
    }

    // ==================== ACCESSOR ====================

    /**
     * Label role dalam Bahasa Indonesia
     */
    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'admin'     => 'Admin',
            'cashier'   => 'Kasir',
            // 'warehouse' => 'Gudang',
            default     => ucfirst($this->role),
        };
    }

    public function getRoleColorAttribute(): string
    {
        return match($this->role) {
            'admin'     => 'bg-purple-100 text-purple-700',
            'cashier'   => 'bg-blue-100 text-blue-700',
            'warehouse' => 'bg-green-100 text-green-700',
            default     => 'bg-gray-100 text-gray-700',
        };
    }
}