<?php
namespace App\Services\Payments;

class BacTransferHandler
{
    public function __construct()
    {
        // config('services.bac.api_key')
    }

    public function initiateTransfer(float $amount, int $orderId): array
    {
        return [
            'code'          => '00',
            'authorization' => 'BAC-' . strtoupper(uniqid()),
            'amount'        => $amount,
            'order_id'      => $orderId,
        ];
    }

    public function cancelTransfer(string $authorization): array
    {
        return ['code' => '00', 'message' => 'Transfer cancelled'];
    }
}
