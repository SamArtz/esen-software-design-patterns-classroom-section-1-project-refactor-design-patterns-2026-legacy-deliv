<?php

namespace App\Models;

class PaymentInCash extends Payment
{
    protected $table = 'payments';

    public function process(): bool
    {
        $this->status = 'completed';
        $this->processed_at = now();
        $this->save();
        return true;
    }

    // Esta subclase lanza excepción en lugar de retornar bool.
    // Código que trata Payment como Payment falla en runtime al encontrar un PaymentInCash.
    public function refund(): bool
    {
        throw new \RuntimeException(
            'Cash payments cannot be refunded through the system. Handle manually.'
        );
    }
}
