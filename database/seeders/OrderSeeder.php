<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Courier;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::where('verified', true)->get();
        $vendors   = Vendor::where('status', 'active')->get();
        $couriers  = Courier::all();
        $products  = Product::where('stock', '>', 0)->get();

        // Distribución: 30 created, 20 paid, 30 preparing, 20 ready, 30 delivered, 15 cancelled, 5 refunded
        $statuses = array_merge(
            array_fill(0, 30, 'created'),
            array_fill(0, 20, 'paid'),
            array_fill(0, 30, 'preparing'),
            array_fill(0, 20, 'ready'),
            array_fill(0, 30, 'delivered'),
            array_fill(0, 15, 'cancelled'),
            array_fill(0, 5, 'refunded')
        );

        $needsCourier = ['ready', 'delivered'];

        foreach ($statuses as $index => $status) {
            $customer  = $customers[$index % $customers->count()];
            $vendor    = $vendors[$index % $vendors->count()];
            $courier   = in_array($status, $needsCourier)
                ? $couriers[$index % $couriers->count()]
                : null;

            $createdAt = Carbon::now()->subDays(rand(1, 60))->subHours(rand(0, 23));

            // Tomar 1-3 productos del vendor para la orden
            $vendorProducts = $products->where('vendor_id', $vendor->id)->take(rand(1, 3));
            if ($vendorProducts->isEmpty()) {
                $vendorProducts = $products->take(2);
            }

            $subtotal = 0.0;
            $orderItemsData = [];
            foreach ($vendorProducts as $product) {
                $qty      = rand(1, 3);
                $price    = $product->price;
                $lineTotal = $price * $qty;
                $subtotal += $lineTotal;
                $orderItemsData[] = [
                    'item_type'  => 'product',
                    'item_id'    => $product->id,
                    'quantity'   => $qty,
                    'unit_price' => $price,
                    'subtotal'   => $lineTotal,
                ];
            }

            if ($subtotal < 5.00) {
                $subtotal = 5.00;
                if (!empty($orderItemsData)) {
                    $orderItemsData[0]['unit_price'] = 5.00;
                    $orderItemsData[0]['subtotal']   = 5.00;
                }
            }

            $deliveryFee = 2.50;
            if ($subtotal > 20.00) $deliveryFee = 1.50;
            if ($subtotal > 50.00) $deliveryFee = 0.00;

            $total = $subtotal + $deliveryFee;

            $order = Order::create([
                'customer_id'      => $customer->id,
                'vendor_id'        => $vendor->id,
                'courier_id'       => $courier?->id,
                'status'           => $status,
                'subtotal'         => round($subtotal, 2),
                'discount_total'   => 0,
                'delivery_fee'     => $deliveryFee,
                'total'            => round($total, 2),
                'delivery_address' => $customer->address,
                'notes'            => null,
                'created_at'       => $createdAt,
                'updated_at'       => $createdAt->copy()->addMinutes(rand(5, 120)),
            ]);

            foreach ($orderItemsData as $itemData) {
                OrderItem::create(array_merge($itemData, [
                    'order_id'   => $order->id,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]));
            }
        }
    }
}
