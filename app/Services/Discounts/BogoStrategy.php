<?php

namespace App\Services\Discounts;

use App\Models\Order;
use App\Models\Discount;

class BogoStrategy implements DiscountStrategy 
{
    public function calculate(Order $order, Discount $discount): float 
    {
        $cheapestPrice = $order->items
            ->map(fn($item) => $item->unit_price)
            ->sort()
            ->values()
            ->first();

        return (float) ($cheapestPrice ?? 0.0);
    }
}