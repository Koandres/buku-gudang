<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = ['kode', 'nama', 'satuan', 'harga_acuan', 'stok_minimum', 'aktif'];

    public function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('aktif', true);
    }

    public function requestItems(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
