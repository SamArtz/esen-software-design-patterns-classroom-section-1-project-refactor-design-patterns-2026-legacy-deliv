<?php

namespace App\Services\Discounts;

use App\Models\Order;
use App\Models\Discount;

class FirstPurchaseStrategy implements DiscountStrategy 
{
    public function calculate(Order $order, Discount $discount): float 
    {
        $previousOrders = Order::where('customer_id', $order->customer_id)
            ->whereIn('status', ['delivered', 'paid', 'accepted', 'preparing', 'ready', 'picked_up'])
            ->where('id', '!=', $order->id)
            ->count();

        if ($previousOrders > 0) {
            return 0.0;
        }

        $calculated = $order->subtotal * ($discount->value / 100);
        
        if ($discount->max_discount_amount !== null) {
            $calculated = min($calculated, $discount->max_discount_amount);
        }
        
        return (float) $calculated;
    }
}