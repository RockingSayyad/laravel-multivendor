<?php
// app/Models/Cart.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function itemsWithProducts()
    {
        return $this->hasMany(CartItem::class)->with('product.vendor');
    }

    /**
     * Return cart items grouped by vendor.
     * Returns a collection keyed by vendor_id with shape:
     * [ vendor_id => [ 'vendor' => Vendor, 'items' => Collection<CartItem> ] ]
     */
    public function groupedByVendor(): \Illuminate\Support\Collection
    {
        return $this->itemsWithProducts()
            ->get()
            ->groupBy(fn ($item) => $item->product->vendor_id)
            ->map(function ($items) {
                return [
                    'vendor'     => $items->first()->product->vendor,
                    'items'      => $items,
                    'subtotal'   => $items->sum(fn ($i) => $i->product->price * $i->quantity),
                ];
            });
    }

    public function totalAmount(): float
    {
        return $this->itemsWithProducts()
            ->get()
            ->sum(fn ($item) => $item->product->price * $item->quantity);
    }

    public function isEmpty(): bool
    {
        return $this->items()->count() === 0;
    }
}
