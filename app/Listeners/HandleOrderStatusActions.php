<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Services\EmailService;
use App\Services\SMSService;
use App\Services\PushService;
use App\Services\InventoryService;
use App\Services\AuditService;
use App\Services\MetricsService;
use App\Support\Logger;
use Illuminate\Support\Facades\Log;

class HandleOrderStatusActions
{
    public function handle(OrderStatusChanged $event): void
    {
        try {
            $order = $event->order;
            $status = $event->status;
            
            // Forzamos la carga de relaciones para que no den null en el test
            $order->loadMissing(['customer.user', 'vendor.user']);

            $this->processNotifications($order, $status);
            $this->processSideEffects($order, $status);
        } catch (\Throwable $e) {
            // Logueamos el error pero permitimos que el controlador de pagos responda 200 OK
            Log::error("Error procesando efectos de la orden: " . $e->getMessage());
        }
    }

    private function processNotifications($order, $status): void
    {
        $emailService = app(EmailService::class);
        $pushService  = app(PushService::class);
        $smsService   = app(SMSService::class);

        $customerEmail = $order->customer?->user?->email;
        $vendorEmail   = $order->vendor?->user?->email;

        if ($status === 'created' && $customerEmail && $vendorEmail) {
            $emailService->send($customerEmail, 'Pedido recibido', "Tu pedido #{$order->id} ha sido recibido.");
            $emailService->send($vendorEmail, 'Nuevo pedido', "Tienes un nuevo pedido #{$order->id}.");
        } elseif ($status === 'paid' && $customerEmail) {
            $emailService->send($customerEmail, 'Pago confirmado', "Tu pago para el pedido #{$order->id} fue procesado.");
            $pushService->send($order->customer_id, 'Pago recibido', "Tu pago fue procesado exitosamente.");
        }

        Logger::getInstance()->log("Order {$order->id} notifications dispatched for status: {$status}");
    }

    private function processSideEffects($order, $status): void
    {
        $inventoryService = app(InventoryService::class);
        $auditService     = app(AuditService::class);
        $metricsService   = app(MetricsService::class);

        if ($status === 'created') {
            $inventoryService->reserveStock($order);
            $auditService->log('order.created', $order->id, $order->toArray());
        } elseif ($status === 'paid') {
            $auditService->log('order.paid', $order->id, ['amount' => $order->total]);
            $metricsService->increment('orders.paid');
        }
    }
}