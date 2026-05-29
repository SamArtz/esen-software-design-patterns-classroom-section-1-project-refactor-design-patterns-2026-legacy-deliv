<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'type', 'value', 'min_order_amount', 'max_discount_amount',
                           'valid_from', 'valid_to', 'max_uses', 'current_uses', 'vendor_id'];

    protected $casts = ['valid_from' => 'datetime', 'valid_to' => 'datetime'];

    public function vendor() { return $this->belongsTo(Vendor::class); }
    public function orders() { return $this->belongsToMany(Order::class, 'order_discounts'); }

    // También: validaciones inline que deberían ser scopes.

    public function isValid(): bool
    {
        return now()->between($this->valid_from, $this->valid_to)
            && ($this->max_uses === null || $this->current_uses < $this->max_uses);
    }
    
    public function apply(Order $order): float
    {
        if (now() < $this->valid_from || now() > $this->valid_to) {
            return 0.0;
        }

        if ($this->max_uses !== null && $this->current_uses >= $this->max_uses) {
            return 0.0;
        }

        if ($this->min_order_amount !== null && $order->subtotal < $this->min_order_amount) {
            return 0.0;
        }

        if ($this->vendor_id !== null && $this->vendor_id !== $order->vendor_id) {
            return 0.0;
        }

        switch ($this->type) {
            case 'percentage':
                $discount = $order->subtotal * ($this->value / 100);
                if ($this->max_discount_amount !== null) {
                    $discount = min($discount, $this->max_discount_amount);
                }
                return (float) $discount;

            case 'fixed_amount':
                return (float) min($this->value, $order->subtotal);

            case 'bogo':
                // Buy one get one: devuelve el precio del item más barato de la orden
                $cheapestPrice = $order->items
                    ->map(fn($item) => $item->unit_price)
                    ->sort()
                    ->values()
                    ->first();
                return (float) ($cheapestPrice ?? 0.0);

            case 'first_purchase':
                // Verifica si es la primera compra del customer
                $previousOrders = Order::where('customer_id', $order->customer_id)
                    ->whereIn('status', ['delivered', 'paid', 'accepted', 'preparing', 'ready', 'picked_up'])
                    ->where('id', '!=', $order->id)
                    ->count();

                if ($previousOrders > 0) {
                    return 0.0;
                }

                $discount = $order->subtotal * ($this->value / 100);
                if ($this->max_discount_amount !== null) {
                    $discount = min($discount, $this->max_discount_amount);
                }
                return (float) $discount;

            case 'free_delivery':
                return (float) $order->delivery_fee;

            default:
                \App\Support\Logger::getInstance()->log(
                    "Unknown discount type '{$this->type}' for discount {$this->id}", 'warning'
                );
                return 0.0;
        }
    }
}
