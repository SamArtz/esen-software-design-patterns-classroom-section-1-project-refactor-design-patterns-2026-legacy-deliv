<?php

namespace App\BadPatterns;

/**
 * ANTI-PATRÓN: Factory innecesario
 *
 * Problema: Se aplica Factory Method cuando no hay variación de creación.
 * Solo existe un tipo de objeto a crear, y nunca va a cambiar.
 * El factory no abstrae nada — solo agrega una capa de indirección sin beneficio.
 *
 * Señal de alerta: si el factory tiene un solo case (o un solo método),
 * probablemente no necesitas un factory. Un `new` directo es más claro.
 *
 * Contexto en el legacy: el sistema necesita generar un recibo de orden.
 * Solo hay un formato de recibo. No hay variación prevista.
 * Alguien aplicó Factory Method "por si acaso" (YAGNI violation).
 */

// El objeto que se crea — simple, sin variantes
class OrderReceipt
{
    public function __construct(
        private int $orderId,
        private float $total,
        private string $customerName
    ) {}

    public function render(): string
    {
        return "Recibo #{$this->orderId} | {$this->customerName} | \${$this->total}";
    }
}

// El factory innecesario — una sola implementación, sin posibilidad de extensión real
abstract class OrderReceiptFactory
{
    abstract public function createReceipt(int $orderId, float $total, string $customerName): OrderReceipt;

    public function generateAndPrint(int $orderId, float $total, string $customerName): string
    {
        $receipt = $this->createReceipt($orderId, $total, $customerName);
        return $receipt->render();
    }
}

class StandardOrderReceiptFactory extends OrderReceiptFactory
{
    public function createReceipt(int $orderId, float $total, string $customerName): OrderReceipt
    {
        return new OrderReceipt($orderId, $total, $customerName);
    }
}

// Uso: tres líneas para lo que debería ser una
// $factory = new StandardOrderReceiptFactory();
// $output  = $factory->generateAndPrint(42, 15.50, 'María García');
//
// Vs. lo que debería ser:
// $receipt = new OrderReceipt(42, 15.50, 'María García');
// $output  = $receipt->render();
