<?php

namespace App\BadPatterns;

/**
 * ANTI-PATRÓN: Facade que se convierte en God Object
 *
 * Problema: Facade simplifica una interfaz compleja exponiendo
 * solo lo que el cliente necesita. El anti-patrón ocurre cuando
 * la Facade acumula operaciones hasta convertirse en el punto de
 * entrada de todo el sistema — un God Object con nombre de Facade.
 *
 * Contexto en el legacy: se creó DeliveryFacade para simplificar
 * el checkout. Con el tiempo, cada nueva feature se agregó aquí
 * porque "ya teníamos la facade". Ahora coordina órdenes, pagos,
 * notificaciones, reportes, usuarios y métricas.
 *
 * Señal de alerta: cuando la Facade tiene más de 8-10 métodos públicos
 * que cubren dominios distintos, ya no es una Facade — es un God Object
 * con un nombre que suena arquitectónico.
 */
class DeliveryFacade
{
    // Coordina el checkout — esto sí es Facade legítimo
    public function placeOrder(int $customerId, array $cart, string $paymentMethod): array
    {
        \App\Support\Logger::getInstance()->log("Facade: placeOrder customer #{$customerId}");
        return ['order_id' => rand(1000, 9999), 'status' => 'created'];
    }

    // Hasta aquí tiene sentido. A partir de aquí empieza el problema:

    // ¿Por qué la Facade de checkout maneja reportes?
    public function generateDailyReport(string $date): string
    {
        \App\Support\Logger::getInstance()->log("Facade: generateDailyReport {$date}");
        return storage_path("app/reports/daily_{$date}.pdf");
    }

    public function generateVendorReport(int $vendorId, string $from, string $to): string
    {
        \App\Support\Logger::getInstance()->log("Facade: generateVendorReport vendor #{$vendorId}");
        return storage_path("app/reports/vendor_{$vendorId}.csv");
    }

    // ¿Por qué la Facade de checkout maneja usuarios?
    public function registerCustomer(array $data): int
    {
        \App\Support\Logger::getInstance()->log("Facade: registerCustomer {$data['email']}");
        return rand(1, 999);
    }

    public function verifyCustomer(int $customerId): bool
    {
        \App\Support\Logger::getInstance()->log("Facade: verifyCustomer #{$customerId}");
        return true;
    }

    public function suspendVendor(int $vendorId, string $reason): void
    {
        \App\Support\Logger::getInstance()->log("Facade: suspendVendor #{$vendorId}: {$reason}");
    }

    // ¿Por qué la Facade de checkout maneja couriers?
    public function assignCourier(int $orderId, int $courierId): void
    {
        \App\Support\Logger::getInstance()->log("Facade: assignCourier order #{$orderId} courier #{$courierId}");
    }

    public function getCourierLocation(int $courierId): array
    {
        return ['lat' => 13.6929, 'lng' => -89.2182];
    }

    // ¿Por qué la Facade de checkout maneja métricas?
    public function getDashboardMetrics(string $period): array
    {
        return [
            'orders_today'   => 142,
            'revenue_today'  => 1850.00,
            'active_vendors' => 23,
            'active_couriers'=> 18,
        ];
    }

    public function getTopVendors(int $limit = 10): array
    {
        return [];
    }

    // ¿Por qué la Facade de checkout envía notificaciones masivas?
    public function sendBulkPromotion(array $customerIds, string $message): int
    {
        \App\Support\Logger::getInstance()->log("Facade: sendBulkPromotion to " . count($customerIds) . " customers");
        return count($customerIds);
    }

    public function notifyVendorsOfSystemMaintenance(string $scheduledAt): void
    {
        \App\Support\Logger::getInstance()->log("Facade: maintenance notification scheduled {$scheduledAt}");
    }
}

// El resultado: todo el sistema depende de DeliveryFacade.
// Cambiar el formato del reporte requiere entender el checkout.
// Testear el registro de usuarios requiere mockear pagos.
// La "simplificación" que prometía Facade se convirtió en el mayor punto de acoplamiento del sistema.
