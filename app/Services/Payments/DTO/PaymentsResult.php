<?php

namespace App\Services\Payments\DTO;

class PaymentsResult
{
    public function __construct(
        public bool $success,
        public ?string $transactionId = null,
        public array $rawResponse = [],
    ) {
    }
}