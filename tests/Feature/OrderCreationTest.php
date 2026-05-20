<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Category;
use App\Models\PaymentProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_place_order(): void
    {
        // Setup
        $paymentProvider = PaymentProvider::create([
            'name' => 'Wompi', 'api_endpoint' => 'https://sandbox.wompi.co/v1',
            'api_key' => 'test', 'enabled' => true, 'priority' => 1,
        ]);

        $vendorUser = User::create(['name' => 'Vendor', 'email' => 'vendor@test.dev', 'password' => 'Password1', 'role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id, 'business_name' => 'Test Vendor',
            'address' => 'Test Address', 'city' => 'San Salvador', 'status' => 'active',
        ]);

        $category = Category::create([
            'vendor_id' => $vendor->id, 'name' => 'General',
            'slug' => 'general', 'display_order' => 0,
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id, 'category_id' => $category->id,
            'name' => 'Test Product', 'price' => 10.00,
            'available' => true, 'stock' => 50,
        ]);

        $customerUser = User::create([
            'name' => 'Customer', 'email' => 'customer@test.dev',
            'password' => 'Password1', 'role' => 'customer',
        ]);
        $customer = Customer::create([
            'user_id' => $customerUser->id, 'address' => 'Colonia Escalón, 75 Av. Norte',
            'city' => 'San Salvador', 'verified' => true,
        ]);

        // Action
        $token = $customerUser->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->postJson('/api/orders', [
            'vendor_id'      => $vendor->id,
            'items'          => [['type' => 'product', 'id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'cash',
        ]);

        // SMELL: assertion débil, solo verifica status code, no los datos de la orden
        $response->assertStatus(201);
        // No verifica: subtotal correcto, delivery_fee, total, stock actualizado, loyalty_points
    }

    public function test_unverified_customer_cannot_place_order(): void
    {
        $vendorUser = User::create(['name' => 'Vendor', 'email' => 'vendor2@test.dev', 'password' => 'Password1', 'role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id, 'business_name' => 'Test Vendor 2',
            'address' => 'Test Address', 'city' => 'San Salvador', 'status' => 'active',
        ]);
        $category = Category::create([
            'vendor_id' => $vendor->id, 'name' => 'General',
            'slug' => 'general2', 'display_order' => 0,
        ]);
        $product = Product::create([
            'vendor_id' => $vendor->id, 'category_id' => $category->id,
            'name' => 'Product', 'price' => 10.00, 'available' => true, 'stock' => 10,
        ]);

        $customerUser = User::create([
            'name' => 'Customer Unverified', 'email' => 'unverified@test.dev',
            'password' => 'Password1', 'role' => 'customer',
        ]);
        Customer::create([
            'user_id' => $customerUser->id, 'address' => 'Test Address',
            'city' => 'San Salvador', 'verified' => false,
        ]);

        $token = $customerUser->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->postJson('/api/orders', [
            'vendor_id'      => $vendor->id,
            'items'          => [['type' => 'product', 'id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(422);
    }
}
