<?php

namespace App\Services\Checkout;

use App\Models\Customer;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentByCard;
use App\Models\PaymentInCash;
use App\Models\PaymentProvider;
use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\Vendor;
use App\Services\EmailService;
use App\Services\Payments\Contracts\PaymentGatewayAdapter;
use App\Services\SMSService;
use App\Support\Logger;
use Illuminate\Support\Facades\DB;

class CheckoutFacade
{
    public function __construct(
        private PaymentGatewayAdapter $paymentGateway,
        private EmailService $email,
        private SMSService $sms,
    ) {
    }

    public function placeOrder(Customer $customer, array $cart, string $paymentMethod): Order
    {
        $this->validateCustomer($customer, $cart);

        $vendor = $this->validateVendor($cart['vendor_id']);

        $orderItems = $this->buildOrderItems($cart['items']);

        $subtotal = $this->calculateSubtotal($orderItems);

        $discount = $this->applyDiscount($cart['discount_code'] ?? null, $subtotal);

        $deliveryFee = $this->calculateDeliveryFee($subtotal, $discount);

        $total = $subtotal - $discount['amount'] + $deliveryFee;

        DB::beginTransaction();

        try {
            $order = $this->persistOrder(
                $customer,
                $cart,
                $orderItems,
                $subtotal,
                $discount,
                $deliveryFee,
                $total
            );

            $this->reserveStock($cart['items']);

            $this->processPayment($order, $paymentMethod, $total);

            $this->sendNotifications($customer, $vendor, $order, $total);

            Logger::getInstance()->log(
                "Order {$order->id} created by customer {$customer->id}. Total: {$total}"
            );

            $customer->loyalty_points += (int) floor($total);
            $customer->save();

            DB::commit();

            return $order;

        } catch (\Exception $e) {
            DB::rollBack();

            Logger::getInstance()->log(
                "Order creation failed for customer {$customer->id}: " . $e->getMessage(),
                'error'
            );

            throw $e;
        }
    }

    private function validateCustomer(Customer $customer, array $cart): void
    {
        if (empty($cart['items'])) {
            throw new \Exception('Cart is empty.');
        }

        if (!$customer->verified) {
            throw new \Exception('Customer account is not verified.');
        }
    }

    private function validateVendor(int $vendorId): Vendor
    {
        $vendor = Vendor::find($vendorId);

        if (!$vendor || $vendor->status !== 'active') {
            throw new \Exception('Vendor is not available.');
        }

        $openingHours = $vendor->opening_hours ?? [];
        $now = now();
        $dayKey = strtolower($now->format('l'));

        if (!empty($openingHours[$dayKey])) {
            $hours = $openingHours[$dayKey];

            if ($hours['closed'] ?? false) {
                throw new \Exception('Vendor is currently closed.');
            }
        }

        return $vendor;
    }

    private function buildOrderItems(array $items): array
    {
        $orderItems = [];

        foreach ($items as $item) {
            if ($item['type'] === 'product') {
                $product = Product::find($item['id']);

                if (!$product || !$product->available) {
                    throw new \Exception("Product {$item['id']} is not available.");
                }

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}.");
                }

                $unitPrice = $product->price;

                $orderItems[] = [
                    'item_type' => 'product',
                    'item_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $unitPrice * $item['quantity'],
                ];
            }

            if ($item['type'] === 'bundle') {
                $bundle = ProductBundle::find($item['id']);

                if (!$bundle || !$bundle->available) {
                    throw new \Exception("Bundle {$item['id']} is not available.");
                }

                $unitPrice = $bundle->getTotalPrice();

                $orderItems[] = [
                    'item_type' => 'bundle',
                    'item_id' => $bundle->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $unitPrice * $item['quantity'],
                ];
            }
        }

        return $orderItems;
    }

    private function calculateSubtotal(array $orderItems): float
    {
        return collect($orderItems)->sum('subtotal');
    }

    private function applyDiscount(?string $discountCode, float $subtotal): array
    {
        $result = [
            'model' => null,
            'amount' => 0.0,
        ];

        if (!$discountCode) {
            return $result;
        }

        $discount = Discount::where('code', $discountCode)->first();

        if (!$discount) {
            return $result;
        }

        if (now() < $discount->valid_from || now() > $discount->valid_to) {
            return $result;
        }

        if ($discount->max_uses && $discount->current_uses >= $discount->max_uses) {
            return $result;
        }

        if ($discount->type === 'percentage') {
            $amount = $subtotal * ($discount->value / 100);

            if ($discount->max_discount_amount) {
                $amount = min($amount, $discount->max_discount_amount);
            }

            $result['amount'] = $amount;
        }

        if ($discount->type === 'fixed_amount') {
            $result['amount'] = min($discount->value, $subtotal);
        }

        if ($discount->type === 'free_delivery') {
            $result['amount'] = 0.0;
        }

        $result['model'] = $discount;

        return $result;
    }

    private function calculateDeliveryFee(float $subtotal, array $discount): float
    {
        $deliveryFee = 2.50;

        if ($subtotal > 20.00) {
            $deliveryFee = 1.50;
        }

        if ($subtotal > 50.00) {
            $deliveryFee = 0.00;
        }

        if ($discount['model'] && $discount['model']->type === 'free_delivery') {
            $deliveryFee = 0.00;
        }

        return $deliveryFee;
    }

    private function persistOrder(
        Customer $customer,
        array $cart,
        array $orderItems,
        float $subtotal,
        array $discount,
        float $deliveryFee,
        float $total
    ): Order {
        $order = Order::create([
            'customer_id' => $customer->id,
            'vendor_id' => $cart['vendor_id'],
            'status' => 'created',
            'subtotal' => $subtotal,
            'discount_total' => $discount['amount'],
            'delivery_fee' => $deliveryFee,
            'total' => $total,
            'delivery_address' => $customer->address,
            'notes' => $cart['notes'] ?? null,
        ]);

        foreach ($orderItems as $itemData) {
            OrderItem::create(array_merge($itemData, [
                'order_id' => $order->id,
            ]));
        }

        if ($discount['model']) {
            $discount['model']->current_uses++;
            $discount['model']->save();

            $order->discounts()->attach($discount['model']->id);
        }

        return $order;
    }

    private function reserveStock(array $items): void
    {
        foreach ($items as $item) {
            if ($item['type'] !== 'product') {
                continue;
            }

            $product = Product::find($item['id']);

            $product->stock -= $item['quantity'];

            if ($product->stock <= 0) {
                $product->available = false;
            }

            $product->save();
        }
    }

    private function processPayment(Order $order, string $paymentMethod, float $total): void
    {
        if ($paymentMethod === 'cash') {
            PaymentInCash::create([
                'order_id' => $order->id,
                'provider_id' => PaymentProvider::where('enabled', true)
                    ->orderBy('priority')
                    ->first()
                    ->id,
                'amount' => $total,
                'currency' => 'USD',
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            $order->status = 'paid';
            $order->save();

            return;
        }

        $result = $this->paymentGateway->charge(
            $order->id,
            $total,
            'USD'
        );

        PaymentByCard::create([
            'order_id' => $order->id,
            'provider_id' => PaymentProvider::where('name', 'Wompi')->first()->id,
            'amount' => $total,
            'currency' => 'USD',
            'status' => $result->success ? 'completed' : 'failed',
            'external_transaction_id' => $result->transactionId,
            'raw_response' => $result->rawResponse,
            'processed_at' => $result->success ? now() : null,
        ]);

        if (!$result->success) {
            throw new \Exception('Payment processing failed.');
        }

        $order->status = 'paid';
        $order->save();
    }

    private function sendNotifications(Customer $customer, Vendor $vendor, Order $order, float $total): void
    {
        $this->email->send(
            $customer->user->email,
            'Pedido confirmado',
            "Tu pedido #{$order->id} ha sido recibido. Total: \${$total}"
        );

        $this->email->send(
            $vendor->user->email,
            'Nuevo pedido',
            "Tienes un nuevo pedido #{$order->id} por \${$total}"
        );

        if ($customer->user->phone) {
            $this->sms->send(
                $customer->user->phone,
                "Pedido #{$order->id} confirmado. Total: \${$total}"
            );
        }
    }
}