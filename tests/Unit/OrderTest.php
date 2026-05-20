<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(string $status): Order
    {
        $vendorUser = User::create(['name' => 'V', 'email' => uniqid().'@v.dev', 'password' => 'Password1', 'role' => 'vendor']);
        $vendor = Vendor::create(['user_id' => $vendorUser->id, 'business_name' => 'V', 'address' => 'A', 'city' => 'B', 'status' => 'active']);
        $customerUser = User::create(['name' => 'C', 'email' => uniqid().'@c.dev', 'password' => 'Password1', 'role' => 'customer']);
        $customer = Customer::create(['user_id' => $customerUser->id, 'address' => 'A', 'city' => 'B', 'verified' => true]);
        return Order::create([
            'customer_id' => $customer->id, 'vendor_id' => $vendor->id,
            'status' => $status, 'subtotal' => 10, 'discount_total' => 0,
            'delivery_fee' => 2.50, 'total' => 12.50, 'delivery_address' => 'Address Test',
        ]);
    }

    public function test_created_to_paid_transition(): void
    {
        $order = $this->makeOrder('created');
        $order->transitionTo('paid');
        $this->assertEquals('paid', $order->status);
    }

    public function test_paid_to_accepted_transition(): void
    {
        $order = $this->makeOrder('paid');
        $order->transitionTo('accepted');
        $this->assertEquals('accepted', $order->status);
    }

    public function test_accepted_to_preparing_transition(): void
    {
        $order = $this->makeOrder('accepted');
        $order->transitionTo('preparing');
        $this->assertEquals('preparing', $order->status);
    }

    public function test_preparing_to_ready_transition(): void
    {
        $order = $this->makeOrder('preparing');
        $order->transitionTo('ready');
        $this->assertEquals('ready', $order->status);
    }

    public function test_ready_to_picked_up_transition(): void
    {
        $order = $this->makeOrder('ready');
        $order->transitionTo('picked_up');
        $this->assertEquals('picked_up', $order->status);
    }

    public function test_picked_up_to_delivered_transition(): void
    {
        $order = $this->makeOrder('picked_up');
        $order->transitionTo('delivered');
        $this->assertEquals('delivered', $order->status);
    }

    public function test_invalid_transition_throws_exception(): void
    {
        $this->expectException(\Exception::class);
        $order = $this->makeOrder('created');
        $order->transitionTo('delivered');
    }

    public function test_cancelled_order_cannot_transition(): void
    {
        $this->expectException(\Exception::class);
        $order = $this->makeOrder('cancelled');
        $order->transitionTo('paid');
    }

    public function test_delivered_order_cannot_transition_to_preparing(): void
    {
        $this->expectException(\Exception::class);
        $order = $this->makeOrder('delivered');
        $order->transitionTo('preparing');
    }
}
