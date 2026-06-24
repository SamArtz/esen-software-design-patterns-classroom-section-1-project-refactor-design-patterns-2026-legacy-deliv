<?php

namespace App\Services\Payments;
use App\Services\Payments\Contracts\PaymentGatewayAdapter;
use App\Services\Payments\DTO\PaymentsResult;

class BacTransferAdapter implements PaymentGatewayAdapter
{
    public function __construct(
        private BacTransferHandler $handler
    ) {
    }

    public function charge(int $orderId, float $amount, string $currency): PaymentsResult
    {
        $response = $this->handler->initiateTransfer($amount, $orderId);

        return new PaymentsResult(
            success: ($response['code'] ?? null) === '00',
            transactionId: $response['authorization'] ?? null,
            rawResponse: $response,
        );
    }
}