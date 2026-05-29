<?php

namespace App\Services\Discounts;

use App\Models\Order;
use App\Models\Discount;

class PercentageStrategy implements DiscountStrategy 
{
    public function calculate(Order $order, Discount $discount): float 
    {
        $calculated = $order->subtotal * ($discount->value / 100);
        
        if ($discount->max_discount_amount !== null) {
            $calculated = min($calculated, $discount->max_discount_amount);
        }
        
        return (float) $calculated;
    }
}