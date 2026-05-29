<?php

namespace App\Services\Discounts;

use App\Models\Order;
use App\Models\Discount;

interface DiscountStrategy 
{
    /**
     * El contrato que todas las estrategias de descuento deben cumplir.
     */
    public function calculate(Order $order, Discount $discount): float;
}