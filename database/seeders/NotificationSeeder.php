<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Notification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::with(['customer', 'vendor'])->take(100)->get();
        $channels = ['email', 'email', 'email', 'email', 'email', 'email',
                     'sms', 'sms', 'sms', 'sms',
                     'push', 'push', 'push',
                     'whatsapp'];

        $count = 0;

        foreach ($orders as $order) {
            // Notificación al customer
            $channel  = $channels[$count % count($channels)];
            $isSent   = ($count % 10) !== 0; // ~10% unsent
            $isEnc    = ($count % 7 === 0);
            $isLogged = ($count % 5 === 0);
            $isSigned = ($count % 11 === 0);

            Notification::create([
                'recipient_id'   => $order->customer_id,
                'recipient_type' => 'customer',
                'channel'        => $channel,
                'subject'        => "Actualización de pedido #{$order->id}",
                'content'        => "Tu pedido #{$order->id} está en estado: {$order->status}.",
                'is_encrypted'   => $isEnc,
                'is_logged'      => $isLogged,
                'is_signed'      => $isSigned,
                'sent'           => $isSent,
                'sent_at'        => $isSent ? now()->subMinutes(rand(1, 120)) : null,
                'attempts'       => $isSent ? 1 : rand(1, 3),
            ]);
            $count++;

            // Notificación al vendor
            $channel = $channels[$count % count($channels)];
            $isSent  = ($count % 10) !== 0;

            Notification::create([
                'recipient_id'   => $order->vendor_id,
                'recipient_type' => 'vendor',
                'channel'        => $channel,
                'subject'        => "Nuevo pedido #{$order->id}",
                'content'        => "Tienes un nuevo pedido #{$order->id} por \${$order->total}.",
                'is_encrypted'   => false,
                'is_logged'      => ($count % 3 === 0),
                'is_signed'      => false,
                'sent'           => $isSent,
                'sent_at'        => $isSent ? now()->subMinutes(rand(1, 120)) : null,
                'attempts'       => 1,
            ]);
            $count++;

            if ($count >= 500) break;
        }

        // Completar hasta ~500 si quedaron pocas órdenes
        while ($count < 500) {
            $customer = Customer::inRandomOrder()->first();
            Notification::create([
                'recipient_id'   => $customer->id,
                'recipient_type' => 'customer',
                'channel'        => $channels[$count % count($channels)],
                'subject'        => 'Notificación del sistema',
                'content'        => 'Mensaje de notificación ' . $count,
                'is_encrypted'   => ($count % 6 === 0),
                'is_logged'      => ($count % 4 === 0),
                'is_signed'      => ($count % 8 === 0),
                'sent'           => ($count % 10) !== 0,
                'sent_at'        => ($count % 10) !== 0 ? now()->subMinutes(rand(1, 200)) : null,
                'attempts'       => 1,
            ]);
            $count++;
        }
    }
}
