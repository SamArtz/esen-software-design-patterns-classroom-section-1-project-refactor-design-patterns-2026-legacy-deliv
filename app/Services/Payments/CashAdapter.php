<?php

namespace App\Services\Payments;
use App\Services\Payments\Contracts\PaymentGatewayAdapter;
use App\Services\Payments\DTO\PaymentsResult;

class CashAdapter implements PaymentGatewayAdapter
{
    public function charge(int $orderId, float $amount, string $currency): PaymentsResult
    {
        return new PaymentsResult(
            success: true,
            transactionId: null,
            rawResponse: [
                'type' => 'cash',
                'order_id' => $orderId,
                'amount' => $amount,
                'currency' => $currency,
            ],
        );
    }
}