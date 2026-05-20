<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

// NOTA: En Eloquent no podemos hacer la clase abstracta pura y seguir usando herencia de modelos.
// Payment existe como modelo concreto también (para polimorfismo en consultas),
// pero tiene subclases que la extienden.
class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['order_id', 'provider_id', 'amount', 'currency', 'status',
                           'external_transaction_id', 'raw_response', 'processed_at'];

    protected $casts = ['raw_response' => 'array', 'processed_at' => 'datetime'];

    public function order()    { return $this->belongsTo(Order::class); }
    public function provider() { return $this->belongsTo(PaymentProvider::class, 'provider_id'); }

    // Contrato que todas las subclases DEBERÍAN respetar
    public function process(): bool
    {
        $this->status = 'completed';
        $this->processed_at = now();
        $this->save();
        return true;
    }

    // Contrato que PaymentInCash ROMPE (LSP violation)
    public function refund(): bool
    {
        $this->status = 'refunded';
        $this->save();
        return true;
    }
}
