<?php

namespace App\BadPatterns;

/**
 * ANTI-PATRÓN: Observer con un solo observer
 *
 * Problema: Observer resuelve el problema de notificar a múltiples
 * interesados sin que el sujeto los conozca directamente.
 * Si solo hay un observer y nunca habrá más, toda la infraestructura
 * (interfaz Observer, lista de observers, attach/detach/notify)
 * es overhead que complica sin beneficio.
 *
 * Contexto en el legacy: cuando una Order cambia de estado,
 * el sistema necesita actualizar el dashboard. Solo eso.
 * Alguien implementó Observer completo "para cuando haya más listeners".
 * Tres meses después, sigue habiendo uno solo.
 *
 * Señal de alerta: un Subject con un solo Observer registrado permanentemente
 * es una llamada a método con pasos extra.
 */
interface OrderObserver
{
    public function onOrderStatusChanged(int $orderId, string $newStatus): void;
}

class OrderSubject
{
    private array $observers = [];

    public function attach(OrderObserver $observer): void
    {
        $this->observers[] = $observer;
    }

    public function detach(OrderObserver $observer): void
    {
        $this->observers = array_filter(
            $this->observers,
            fn($o) => $o !== $observer
        );
    }

    public function notify(int $orderId, string $newStatus): void
    {
        foreach ($this->observers as $observer) {
            $observer->onOrderStatusChanged($orderId, $newStatus);
        }
    }
}

// El único observer que existe — y el único que existirá por ahora
class DashboardUpdater implements OrderObserver
{
    public function onOrderStatusChanged(int $orderId, string $newStatus): void
    {
        \App\Support\Logger::getInstance()->log(
            "Dashboard: order #{$orderId} changed to '{$newStatus}'"
        );
        // Actualiza contadores del dashboard en caché
    }
}

// Uso: cinco líneas para lo que podría ser una llamada directa
// $subject  = new OrderSubject();
// $updater  = new DashboardUpdater();
// $subject->attach($updater);
// $subject->notify($order->id, 'delivered');
//
// Vs. lo que debería ser mientras solo hay un listener:
// $updater = new DashboardUpdater();
// $updater->onOrderStatusChanged($order->id, 'delivered');
//
// Cuándo SÍ tendría sentido: cuando además del dashboard,
// hay un sistema de métricas, un servicio de auditoría,
// y un motor de reglas de negocio que reaccionan al mismo evento.
