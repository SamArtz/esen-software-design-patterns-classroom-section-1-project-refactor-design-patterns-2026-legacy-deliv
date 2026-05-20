<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentProvider;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $wompi      = PaymentProvider::where('name', 'Wompi')->first();
        $n1co       = PaymentProvider::where('name', 'N1co')->first();
        $bacTransfer= PaymentProvider::where('name', 'BAC Transfer')->first();

        // Órdenes que necesitan payment (excluye 'created' y 'cancelled' sin pago)
        $ordersNeedingPayment = Order::whereIn('status', [
            'paid', 'accepted', 'preparing', 'ready', 'picked_up', 'delivered', 'refunded',
        ])->get();

        $cashCount = 0;
        $providers = [$wompi, $n1co, $bacTransfer];

        foreach ($ordersNeedingPayment as $index => $order) {
            $isCash = $cashCount < 5; // Primeros 5 son PaymentInCash
            $provider = $isCash ? $wompi : $providers[$index % count($providers)];

            $paymentStatus = 'completed';
            if ($order->status === 'refunded') $paymentStatus = 'refunded';

            Payment::create([
                'order_id'                => $order->id,
                'provider_id'             => $provider->id,
                'amount'                  => $order->total,
                'currency'                => 'USD',
                'status'                  => $paymentStatus,
                'external_transaction_id' => $isCash ? null : strtoupper('TXN-' . uniqid()),
                'raw_response'            => $isCash
                    ? ['type' => 'cash']
                    : ['provider' => $provider->name, 'status' => 'approved'],
                'processed_at'            => now()->subMinutes(rand(5, 1440)),
                // SMELL: PaymentInCash no distingue en la tabla, solo en el objeto PHP
                // Para demostrar la LSP violation, al menos 1 orden delivered tiene un payment
                // que en tiempo de ejecución se comporta como PaymentInCash
            ]);

            if ($isCash) $cashCount++;
        }
    }
}
