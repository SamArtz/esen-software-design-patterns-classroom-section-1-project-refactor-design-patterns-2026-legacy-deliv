<?php

namespace App\Services\Payments\Contracts;
use App\Services\Payments\DTO\PaymentsResult;

interface PaymentGatewayAdapter
{
    public function charge(int $orderId, float $amount, string $currency): PaymentsResult;
}