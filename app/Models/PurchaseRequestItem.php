<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRequestItem extends Model
{
    use HasFactory;

    protected $fillable = ['purchase_request_id', 'item_id', 'jumlah', 'jumlah_dipesan'];

    public function request(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function sisa(): int
    {
        return $this->jumlah - $this->jumlah_dipesan;
    }
}
