<?php

namespace App\Models;

class PaymentByWallet extends Payment
{
    protected $table = 'payments';

    public function process(): bool
    {
        $this->status = 'completed';
        $this->processed_at = now();
        $this->save();
        return true;
    }

    public function refund(): bool
    {
        $this->status = 'refunded';
        $this->save();
        return true;
    }
}
