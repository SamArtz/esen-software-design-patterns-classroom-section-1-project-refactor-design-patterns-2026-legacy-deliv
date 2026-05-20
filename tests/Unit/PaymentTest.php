<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Payment;
use App\Models\PaymentInCash;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\User;
use App\Models\PaymentProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private function makePayment(): Payment
    {
        $vendorUser = User::create(['name' => 'V', 'email' => uniqid().'@v.dev', 'password' => 'Password1', 'role' => 'vendor']);
        $vendor = Vendor::create(['user_id' => $vendorUser->id, 'business_name' => 'V', 'address' => 'A', 'city' => 'B', 'status' => 'active']);
        $customerUser = User::create(['name' => 'C', 'email' => uniqid().'@c.dev', 'password' => 'Password1', 'role' => 'customer']);
        $customer = Customer::create(['user_id' => $customerUser->id, 'address' => 'A', 'city' => 'B', 'verified' => true]);
        $provider = PaymentProvider::create(['name' => 'Test', 'api_endpoint' => 'x', 'api_key' => 'k', 'enabled' => true, 'priority' => 1]);
        $order = Order::create([
            'customer_id' => $customer->id, 'vendor_id' => $vendor->id,
            'status' => 'paid', 'subtotal' => 10, 'discount_total' => 0,
            'delivery_fee' => 2.50, 'total' => 12.50, 'delivery_address' => 'A',
        ]);
        return Payment::create([
            'order_id' => $order->id, 'provider_id' => $provider->id,
            'amount' => 12.50, 'currency' => 'USD', 'status' => 'pending',
        ]);
    }

    public function test_payment_process_sets_completed_status(): void
    {
        $payment = $this->makePayment();
        $result  = $payment->process();
        $this->assertTrue($result);
        $this->assertEquals('completed', $payment->status);
    }

    public function test_payment_refund_sets_refunded_status(): void
    {
        $payment = $this->makePayment();
        $result  = $payment->refund();
        $this->assertTrue($result);
        $this->assertEquals('refunded', $payment->status);
    }

    public function test_cash_payment_process_sets_completed_status(): void
    {
        $payment = $this->makePayment();
        $cash = PaymentInCash::find($payment->id);
        $result = $cash->process();
        $this->assertTrue($result);
        $this->assertEquals('completed', $cash->status);
    }

    public function test_cash_payment_refund_throws_exception(): void
    {
        $this->expectException(\RuntimeException::class);
        $payment = $this->makePayment();
        $cash = PaymentInCash::find($payment->id);
        $cash->refund();
    }
}
