<?php

namespace App\Models;

class PaymentByCard extends Payment
{
    protected $table = 'payments';

    public function process(): bool
    {
        // Simulado: en producción llamaría a la pasarela
        $this->status = 'completed';
        $this->processed_at = now();
        $this->save();
        return true;
    }

    public function refund(): bool
    {
        // Respeta el contrato del padre
        $this->status = 'refunded';
        $this->save();
        return true;
    }
}
