<?php

namespace App\Services\Payments;
use App\Services\Payments\Contracts\PaymentGatewayAdapter;
use App\Services\Payments\DTO\PaymentsResult;

class WompiAdapter implements PaymentGatewayAdapter
{
    public function __construct(
        private WompiHandler $handler
    ) {
    }

    public function charge(int $orderId, float $amount, string $currency): PaymentsResult
    {
        $response = $this->handler->cobrar($amount, $currency, [
            'referencia' => "ORDER-{$orderId}",
            'descripcion' => "Pago orden #{$orderId}",
        ]);

        return new PaymentsResult(
            success: ($response['estado'] ?? null) === 'APROBADO',
            transactionId: $response['id_transaccion'] ?? null,
            rawResponse: $response,
        );
    }
}