<?php

namespace App\BadPatterns;

/**
 * ANTI-PATRÓN: Strategy con una sola estrategia
 *
 * Problema: Strategy resuelve el problema de variación de algoritmos.
 * Si solo existe una variante y no hay variación prevista, la abstracción
 * es overhead puro: una interfaz, una clase concreta, y un contexto —
 * tres archivos para lo que podría ser un método.
 *
 * Contexto en el legacy: el sistema calcula el delivery_fee de una orden.
 * Actualmente solo hay una regla: tarifa fija de $2.50.
 * Alguien anticipó que "en el futuro habrá múltiples estrategias de precio"
 * y construyó toda la estructura de Strategy. Esa variación nunca llegó.
 *
 * Señal de alerta: una interfaz con un solo implementador que lleva
 * más de 3 meses sin un segundo implementador no necesita ser una interfaz.
 */
interface DeliveryFeeStrategy
{
    public function calculate(float $orderSubtotal, float $distanceKm): float;
}

// La única estrategia que existe — y que ha existido desde siempre
class FlatRateDeliveryFeeStrategy implements DeliveryFeeStrategy
{
    private float $flatRate;

    public function __construct(float $flatRate = 2.50)
    {
        $this->flatRate = $flatRate;
    }

    public function calculate(float $orderSubtotal, float $distanceKm): float
    {
        return $this->flatRate;
    }
}

// El contexto que nadie pidió
class DeliveryFeeCalculator
{
    public function __construct(private DeliveryFeeStrategy $strategy) {}

    public function getFee(float $orderSubtotal, float $distanceKm): float
    {
        return $this->strategy->calculate($orderSubtotal, $distanceKm);
    }
}

// Uso: cuatro líneas para lo que debería ser una constante
// $calculator = new DeliveryFeeCalculator(new FlatRateDeliveryFeeStrategy(2.50));
// $fee = $calculator->getFee($order->subtotal, $distanceKm);
//
// Vs. lo que debería ser mientras solo hay una regla:
// $fee = 2.50;
//
// Cuándo SÍ tendría sentido: cuando ya existe PercentageDeliveryFeeStrategy,
// DistanceBasedDeliveryFeeStrategy, y FreeDeliveryForVIPStrategy en producción.
