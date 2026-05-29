<?php

namespace App\Services\Discounts;

use App\Models\Order;
use App\Models\Discount;

class FreeDeliveryStrategy implements DiscountStrategy 
{
    /**
     * Calcula el descuento de envío gratis.
     * Retorna el monto exacto del costo de envío para que se reste del total.
     */
    public function calculate(Order $order, Discount $discount): float 
    {
        return (float) ($order->delivery_fee ?? 0.0);
    }
}