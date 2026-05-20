<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class DiscountApplicationTest extends TestCase
{
    use RefreshDatabase;

    // SMELL: solo cubre tipo 'percentage'. No hay tests para bogo, first_purchase, free_delivery.
    public function test_percentage_discount_applies_correctly(): void
    {
        $vendorUser = User::create(['name' => 'V', 'email' => 'v@t.dev', 'password' => 'Password1', 'role' => 'vendor']);
        $vendor = Vendor::create(['user_id' => $vendorUser->id, 'business_name' => 'V', 'address' => 'A', 'city' => 'B', 'status' => 'active']);
        $customerUser = User::create(['name' => 'C', 'email' => 'c@t.dev', 'password' => 'Password1', 'role' => 'customer']);
        $customer = Customer::create(['user_id' => $customerUser->id, 'address' => 'Address Test', 'city' => 'San Salvador', 'verified' => true]);

        $order = Order::create([
            'customer_id' => $customer->id, 'vendor_id' => $vendor->id,
            'status' => 'created', 'subtotal' => 20.00, 'discount_total' => 0,
            'delivery_fee' => 2.50, 'total' => 22.50, 'delivery_address' => 'Address Test',
        ]);

        $discount = Discount::create([
            'code'       => 'TEST10',
            'type'       => 'percentage',
            'value'      => 10,
            'valid_from' => Carbon::now()->subDay(),
            'valid_to'   => Carbon::now()->addDay(),
            'max_uses'   => 100,
            'current_uses' => 0,
        ]);

        $applied = $discount->apply($order);

        $this->assertEquals(2.00, $applied); // 10% de 20.00
        // SMELL: no hay tests para bogo, first_purchase, free_delivery
    }
}
