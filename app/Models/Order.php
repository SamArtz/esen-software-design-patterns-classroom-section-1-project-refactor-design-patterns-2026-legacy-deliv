<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Events\OrderStatusChanged;


class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['customer_id', 'vendor_id', 'courier_id', 'status',
                           'subtotal', 'discount_total', 'delivery_fee', 'total',
                           'delivery_address', 'notes'];

    public function customer()   { return $this->belongsTo(Customer::class); }
    public function vendor()     { return $this->belongsTo(Vendor::class); }
    public function courier()    { return $this->belongsTo(Courier::class); }
    public function items()      { return $this->hasMany(OrderItem::class); }
    public function discounts()  { return $this->belongsToMany(Discount::class, 'order_discounts'); }
    public function payment()    { return $this->hasOne(Payment::class); }
    public function notifications() { return $this->hasMany(Notification::class, 'recipient_id')
        ->where('recipient_type', 'customer'); }

    public function transitionTo(string $newStatus): void
    {
        $allowed = [
            'created'   => ['paid', 'cancelled'],
            'paid'      => ['accepted', 'cancelled', 'refunded'],
            'accepted'  => ['preparing', 'cancelled'],
            'preparing' => ['ready'],
            'ready'     => ['picked_up'],
            'picked_up' => ['delivered'],
            'delivered' => ['refunded', 'cancelled'],
            'cancelled' => [],
            'refunded'  => [],
        ];

        if (!isset($allowed[$this->status])) {
            throw new \DomainException("Estado actual desconocido: {$this->status}");
        }

        if (!in_array($newStatus, $allowed[$this->status])) {
            throw new \DomainException(
                "Transición no permitida de '{$this->status}' a '{$newStatus}'."
            );
        }

        $this->status = $newStatus;
        try {
            $this->save();
        } catch (\Exception $e) {
            dd($e->getMessage()); // Si el error es al guardar en Postgres, lo verás aquí
        }
        
        // CORRECCIÓN: Aseguramos que el cambio persistir en la DB siempre.
        $this->save();
        \App\Events\OrderStatusChanged::dispatch($this, $newStatus);

        // Disparamos un evento de Laravel en lugar de llamar a notify() directamente
        // Esto nos permitirá sacar la lógica de notificaciones del modelo más adelante.
        //event("order.status.{$newStatus}", $this);
    }

    public function validateOrder(): bool
    {
        $this->validateBasicRequirements();
        $this->validateInventoryAvailability();
        $this->validateDiscountsIntegrity();

        \App\Support\Logger::getInstance()->log("Order {$this->id} validated successfully.");
        return true;
    }

    private function validateBasicRequirements(): void
    {
        if (!$this->customer || !$this->customer->verified) {
            throw new \Exception('Customer account is not verified.');
        }
        if (!$this->vendor || $this->vendor->status !== 'active') {
            throw new \Exception('Vendor is not currently active.');
        }
        if (empty($this->delivery_address) || strlen($this->delivery_address) < 10) {
            throw new \Exception('Delivery address is required.');
        }
        if ($this->subtotal < 5.00) {
            throw new \Exception('Minimum order amount is $5.00.');
        }
    }

    private function validateInventoryAvailability(): void
    {
        if (!$this->items || $this->items->isEmpty()) {
            throw new \Exception('Order has no items.');
        }

        foreach ($this->items as $item) {
            if ($item->item_type === 'product') {
                $product = Product::find($item->item_id); // SMELL: Debería ser inyectado, pero lo arreglaremos luego.
                if (!$product) {
                    throw new \Exception("Product ID {$item->item_id} no longer exists.");
                }
                if (!$product->available) {
                    throw new \Exception("Product '{$product->name}' is no longer available.");
                }
                if ($product->stock < $item->quantity) {
                    throw new \Exception(
                        "Insufficient stock for '{$product->name}': {$product->stock} available, {$item->quantity} requested."
                    );
                }
            } elseif ($item->item_type === 'bundle') {
                $bundle = ProductBundle::find($item->item_id);
                if (!$bundle) {
                    throw new \Exception("Bundle ID {$item->item_id} no longer exists.");
                }
                if (!$bundle->available) {
                    throw new \Exception("Bundle '{$bundle->name}' is not available.");
                }
            }
        } 
    }


    
    private function validateDiscountsIntegrity(): void
    {
        foreach ($this->discounts as $discount) {
            if (now() > $discount->valid_to) {
                throw new \Exception("Discount '{$discount->code}' has expired.");
            }
            if ($discount->vendor_id && $discount->vendor_id !== $this->vendor_id) {
                throw new \Exception("Discount '{$discount->code}' is not valid for this vendor.");
            }
        }
    }

    public function scopeForUser($query, $user)
    {
    return match ($user->role) {
        'customer' => $query->where('customer_id', $user->customer->id),
        'vendor'   => $query->where('vendor_id', $user->vendor->id),
        'courier'  => $query->where('courier_id', $user->courier->id),
        'admin'    => $query,
        default    => $query->whereRaw('1 = 0'), // Retorna vacío
    };
    }

    

  
}
