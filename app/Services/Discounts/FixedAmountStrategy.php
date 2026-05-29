<?php

namespace App\Services\Discounts;

use App\Models\Order;
use App\Models\Discount;

class FixedAmountStrategy implements DiscountStrategy 
{
    public function calculate(Order $order, Discount $discount): float 
    {
        return (float) min($discount->value, $order->subtotal);
    }
}