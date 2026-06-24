<?php

namespace App\Services\Payments;
use App\Services\Payments\Contracts\PaymentGatewayAdapter;
use App\Services\Payments\DTO\PaymentsResult;

class N1coAdapter implements PaymentGatewayAdapter
{
    public function __construct(
        private N1coHandler $handler
    ) {
    }

    public function charge(int $orderId, float $amount, string $currency): PaymentsResult
    {
        $response = $this->handler->makePayment([
            'amount' => (int) ($amount * 100),
            'currency' => $currency,
            'order_ref' => $orderId,
        ]);

        return new PaymentsResult(
            success: ($response['status'] ?? null) === 'success',
            transactionId: $response['payment_id'] ?? null,
            rawResponse: $response,
        );
    }
}