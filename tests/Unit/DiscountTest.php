<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class DiscountTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(float $subtotal = 20.00): Order
    {
        $vendorUser = User::create(['name' => 'V', 'email' => uniqid().'@v.dev', 'password' => 'Password1', 'role' => 'vendor']);
        $vendor = Vendor::create(['user_id' => $vendorUser->id, 'business_name' => 'V', 'address' => 'A', 'city' => 'B', 'status' => 'active']);
        $customerUser = User::create(['name' => 'C', 'email' => uniqid().'@c.dev', 'password' => 'Password1', 'role' => 'customer']);
        $customer = Customer::create(['user_id' => $customerUser->id, 'address' => 'A', 'city' => 'B', 'verified' => true]);
        return Order::create([
            'customer_id' => $customer->id, 'vendor_id' => $vendor->id,
            'status' => 'created', 'subtotal' => $subtotal, 'discount_total' => 0,
            'delivery_fee' => 2.50, 'total' => $subtotal + 2.50, 'delivery_address' => 'A',
        ]);
    }

    public function test_percentage_discount(): void
    {
        $discount = Discount::create([
            'code' => 'PCT10', 'type' => 'percentage', 'value' => 10,
            'valid_from' => Carbon::now()->subDay(), 'valid_to' => Carbon::now()->addDay(),
        ]);
        $order = $this->makeOrder(20.00);
        $this->assertEquals(2.00, $discount->apply($order));
    }

    public function test_fixed_amount_discount(): void
    {
        $discount = Discount::create([
            'code' => 'FIXED5', 'type' => 'fixed_amount', 'value' => 5.00,
            'valid_from' => Carbon::now()->subDay(), 'valid_to' => Carbon::now()->addDay(),
        ]);
        $order = $this->makeOrder(20.00);
        $this->assertEquals(5.00, $discount->apply($order));
    }

    public function test_expired_discount_returns_zero(): void
    {
        $discount = Discount::create([
            'code' => 'EXP', 'type' => 'percentage', 'value' => 10,
            'valid_from' => Carbon::now()->subDays(10), 'valid_to' => Carbon::now()->subDays(1),
        ]);
        $order = $this->makeOrder(20.00);
        $this->assertEquals(0.0, $discount->apply($order));
    }

    public function test_bogo_discount_returns_cheapest_item_price(): void
    {
        $discount = Discount::create([
            'code' => 'BOGO', 'type' => 'bogo', 'value' => 0,
            'valid_from' => Carbon::now()->subDay(), 'valid_to' => Carbon::now()->addDay(),
        ]);
        $order = $this->makeOrder(20.00);
        $order->items()->create(['item_type' => 'product', 'item_id' => 1, 'quantity' => 1, 'unit_price' => 8.00, 'subtotal' => 8.00]);
        $order->items()->create(['item_type' => 'product', 'item_id' => 2, 'quantity' => 1, 'unit_price' => 12.00, 'subtotal' => 12.00]);
        $order->load('items');
        $this->assertEquals(8.00, $discount->apply($order));
    }

    public function test_first_purchase_discount_applies_for_new_customer(): void
    {
        $discount = Discount::create([
            'code' => 'FIRST', 'type' => 'first_purchase', 'value' => 20,
            'valid_from' => Carbon::now()->subDay(), 'valid_to' => Carbon::now()->addDay(),
        ]);
        $order = $this->makeOrder(20.00);
        $this->assertEquals(4.00, $discount->apply($order));
    }

    public function test_first_purchase_discount_does_not_apply_for_returning_customer(): void
    {
        $discount = Discount::create([
            'code' => 'FIRST2', 'type' => 'first_purchase', 'value' => 20,
            'valid_from' => Carbon::now()->subDay(), 'valid_to' => Carbon::now()->addDay(),
        ]);
        $firstOrder = $this->makeOrder(20.00);
        Order::where('id', $firstOrder->id)->update(['status' => 'delivered']);

        $secondOrder = Order::create([
            'customer_id' => $firstOrder->customer_id, 'vendor_id' => $firstOrder->vendor_id,
            'status' => 'created', 'subtotal' => 20.00, 'discount_total' => 0,
            'delivery_fee' => 2.50, 'total' => 22.50, 'delivery_address' => 'A',
        ]);

        $this->assertEquals(0.0, $discount->apply($secondOrder));
    }

    public function test_free_delivery_discount_returns_delivery_fee(): void
    {
        $discount = Discount::create([
            'code' => 'FREEDEL', 'type' => 'free_delivery', 'value' => 0,
            'valid_from' => Carbon::now()->subDay(), 'valid_to' => Carbon::now()->addDay(),
        ]);
        $order = $this->makeOrder(20.00);
        $this->assertEquals(2.50, $discount->apply($order));
    }

    public function test_discount_does_not_apply_when_max_uses_reached(): void
    {
        $discount = Discount::create([
            'code' => 'MAXED', 'type' => 'percentage', 'value' => 10,
            'valid_from' => Carbon::now()->subDay(), 'valid_to' => Carbon::now()->addDay(),
            'max_uses' => 5, 'current_uses' => 5,
        ]);
        $order = $this->makeOrder(20.00);
        $this->assertEquals(0.0, $discount->apply($order));
    }

    public function test_discount_does_not_apply_below_minimum_order_amount(): void
    {
        $discount = Discount::create([
            'code' => 'MIN15', 'type' => 'percentage', 'value' => 10,
            'valid_from' => Carbon::now()->subDay(), 'valid_to' => Carbon::now()->addDay(),
            'min_order_amount' => 15.00,
        ]);
        $order = $this->makeOrder(10.00);
        $this->assertEquals(0.0, $discount->apply($order));
    }
}
