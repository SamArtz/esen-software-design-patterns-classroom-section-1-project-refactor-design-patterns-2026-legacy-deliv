<?php

// Anti-pattern: Facade con 15+ métodos que contienen toda la lógica del sistema.
// No delega a subsistemas especializados - implementa directamente.
// En S9 se enseña el Facade real como simplificador, no como god class.
class KitchenSinkFacade
{
    // SMELL: implementa directamente en lugar de delegar
    public function registerUser(string $name, string $email, string $password, string $role): array
    {
        // Debería delegar a UserService, AuthService, etc.
        $hash = password_hash($password, PASSWORD_BCRYPT);
        return ['id' => rand(1, 9999), 'name' => $name, 'email' => $email, 'role' => $role, 'password' => $hash];
    }

    public function loginUser(string $email, string $password): string
    {
        // Debería delegar a AuthService
        return 'fake_token_' . md5($email . $password);
    }

    public function createOrder(array $items, int $customerId, int $vendorId): array
    {
        // Debería delegar a OrderService
        $subtotal = array_sum(array_column($items, 'price'));
        return ['id' => rand(1, 9999), 'subtotal' => $subtotal, 'status' => 'created'];
    }

    public function processPayment(int $orderId, string $provider, float $amount): bool
    {
        // Debería delegar a PaymentService
        error_log("Processing $amount via $provider for order $orderId");
        return true;
    }

    public function sendEmail(string $to, string $subject, string $body): bool
    {
        error_log("[EMAIL] $to: $subject");
        return true;
    }

    public function sendSms(string $phone, string $message): bool
    {
        error_log("[SMS] $phone: $message");
        return true;
    }

    public function generateReport(string $type, array $params): string
    {
        return "Report of type $type generated";
    }

    public function applyDiscount(string $code, float $subtotal): float
    {
        return $subtotal * 0.9; // 10% hardcoded
    }

    public function updateCourierLocation(int $courierId, float $lat, float $lng): void
    {
        error_log("Courier $courierId at $lat,$lng");
    }

    public function assignCourier(int $orderId, int $courierId): void
    {
        error_log("Courier $courierId assigned to order $orderId");
    }

    public function cancelOrder(int $orderId, string $reason): bool
    {
        error_log("Order $orderId cancelled: $reason");
        return true;
    }

    public function refundPayment(int $paymentId, float $amount): bool
    {
        error_log("Refund $amount for payment $paymentId");
        return true;
    }

    public function getVendorStats(int $vendorId): array
    {
        return ['total_orders' => rand(10, 500), 'revenue' => rand(100, 5000)];
    }

    public function updateProductStock(int $productId, int $quantity): void
    {
        error_log("Product $productId stock updated to $quantity");
    }

    public function calculateDeliveryFee(float $subtotal, float $distance): float
    {
        if ($subtotal > 50) return 0.0;
        if ($subtotal > 20) return 1.50;
        return 2.50;
    }
}
