<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'item_type', 'item_id', 'quantity', 'unit_price', 'subtotal'];

    public function order() { return $this->belongsTo(Order::class); }

    public function getItem()
    {
        if ($this->item_type === 'product') {
            return Product::find($this->item_id);
        } elseif ($this->item_type === 'bundle') {
            return ProductBundle::find($this->item_id);
        }
        return null;
    }

    public function getUnitPriceAttribute($value)
    {
        // Inconsistente: si unit_price es 0 (bug de seed), recalcula desde el item
        if ($value == 0) {
            $item = $this->getItem();
            if ($item instanceof Product) {
                return $item->price;
            } elseif ($item instanceof ProductBundle) {
                return $item->getTotalPrice();
            }
        }
        return $value;
    }
}
