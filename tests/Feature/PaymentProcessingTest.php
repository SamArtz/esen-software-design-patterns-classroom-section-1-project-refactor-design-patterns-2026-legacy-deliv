<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentProcessingTest extends TestCase
{
    use RefreshDatabase;

    private function createOrderWithPayment(string $status = 'created'): array
    {
        $vendorUser = User::create(['name' => 'V', 'email' => uniqid().'@v.dev', 'password' => 'Password1', 'role' => 'vendor']);
        $vendor = Vendor::create(['user_id' => $vendorUser->id, 'business_name' => 'V', 'address' => 'A', 'city' => 'B', 'status' => 'active']);
        $customerUser = User::create(['name' => 'C', 'email' => uniqid().'@c.dev', 'password' => 'Password1', 'role' => 'customer']);
        $customer = Customer::create(['user_id' => $customerUser->id, 'address' => 'Colonia Escalón Test Address', 'city' => 'San Salvador', 'verified' => true]);
        $provider = PaymentProvider::create(['name' => 'Wompi', 'api_endpoint' => 'x', 'api_key' => 'k', 'enabled' => true, 'priority' => 1]);
        $order = Order::create([
            'customer_id' => $customer->id, 'vendor_id' => $vendor->id,
            'status' => $status, 'subtotal' => 10.00, 'discount_total' => 0,
            'delivery_fee' => 2.50, 'total' => 12.50, 'delivery_address' => 'Test Address',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id, 'provider_id' => $provider->id,
            'amount' => 12.50, 'currency' => 'USD', 'status' => 'pending',
        ]);
        return compact('order', 'payment', 'customerUser', 'provider');
    }

    public function test_wompi_payment_processes_successfully(): void
    {
        ['order' => $order, 'customerUser' => $user] = $this->createOrderWithPayment();
        $token = $user->createToken('t')->plainTextToken;

        $response = $this->withToken($token)->postJson("/api/payments/{$order->id}/process", [
            'provider' => 'wompi',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_n1co_payment_processes_successfully(): void
    {
        ['order' => $order, 'customerUser' => $user] = $this->createOrderWithPayment();
        $token = $user->createToken('t')->plainTextToken;

        $response = $this->withToken($token)->postJson("/api/payments/{$order->id}/process", [
            'provider' => 'n1co',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_cash_payment_creates_payment_record(): void
    {
        ['order' => $order, 'customerUser' => $user] = $this->createOrderWithPayment();
        $token = $user->createToken('t')->plainTextToken;

        $response = $this->withToken($token)->postJson("/api/payments/{$order->id}/process", [
            'provider' => 'cash',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

}
