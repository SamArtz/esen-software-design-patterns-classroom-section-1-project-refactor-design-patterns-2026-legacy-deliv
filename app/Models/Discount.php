<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\Discounts\PercentageStrategy;
use App\Services\Discounts\FixedAmountStrategy;
use App\Services\Discounts\FirstPurchaseStrategy;
use App\Services\Discounts\BogoStrategy;
use App\Services\Discounts\FreeDeliveryStrategy;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'type', 'value', 'min_order_amount', 'max_discount_amount',
                           'valid_from', 'valid_to', 'max_uses', 'current_uses', 'vendor_id'];

    protected $casts = ['valid_from' => 'datetime', 'valid_to' => 'datetime'];

    public function vendor() { return $this->belongsTo(Vendor::class); }
    public function orders() { return $this->belongsToMany(Order::class, 'order_discounts'); }

    // También: validaciones inline que deberían ser scopes.

    public function isValid(): bool
    {
        return now()->between($this->valid_from, $this->valid_to)
            && ($this->max_uses === null || $this->current_uses < $this->max_uses);
    }
    
    public function apply(Order $order): float
    {
        if (!$this->isActive() || !$this->isApplicableTo($order)) {
            return 0.0;
        }

            $strategies = [
            'percentage'     => PercentageStrategy::class,
            'fixed_amount'   => FixedAmountStrategy::class,
            'first_purchase' => FirstPurchaseStrategy::class,
            'bogo'           => BogoStrategy::class,
            'free_delivery'  => FreeDeliveryStrategy::class,
            ];

            $strategyClass = $strategies[strtolower($this->type)] ?? null;

        if (!$strategyClass || !class_exists($strategyClass)) {
            \App\Support\Logger::getInstance()->log(
                "Unknown discount type '{$this->type}' for discount {$this->id}", 'warning'
            );
            return 0.0;
        }

        // Instanciamos dinámicamente la estrategia necesaria
        $strategy = new $strategyClass();
        
        return $strategy->calculate($order, $this);
    }

        public function isActive(): bool
    {
        // Usamos now() de forma consistente con tu lógica original
        return now()->between($this->valid_from, $this->valid_to)
            && ($this->max_uses === null || $this->current_uses < $this->max_uses);
    }

    /**
     * Verifica si el descuento cumple las restricciones de monto mínimo y vendedor.
     */
    public function isApplicableTo(Order $order): bool
    {
        if ($this->min_order_amount !== null && $order->subtotal < $this->min_order_amount) {
            return false;
        }

        if ($this->vendor_id !== null && $this->vendor_id !== $order->vendor_id) {
            return false;
        }

        return true;
    }
}
