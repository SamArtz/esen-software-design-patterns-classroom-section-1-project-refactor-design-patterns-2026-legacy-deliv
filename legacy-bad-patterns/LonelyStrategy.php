<?php

// Anti-pattern: Strategy con una sola implementación concreta.
// El patrón no añade valor aquí. El contexto nunca cambia de estrategia.
// En S6 se discute cuándo Strategy agrega valor vs cuándo es over-engineering.

interface DiscountStrategy
{
    public function calculate(float $subtotal): float;
}

// Única implementación concreta. Nunca hay otra.
class PercentageOnlyStrategy implements DiscountStrategy
{
    public function __construct(private float $percentage) {}

    public function calculate(float $subtotal): float
    {
        return $subtotal * ($this->percentage / 100);
    }
}

// El contexto nunca cambia de estrategia en tiempo de ejecución
class DiscountContext
{
    private DiscountStrategy $strategy;

    public function __construct()
    {
        // Hardcoded: nunca se cambia la estrategia
        $this->strategy = new PercentageOnlyStrategy(10.0);
    }

    public function apply(float $subtotal): float
    {
        return $this->strategy->calculate($subtotal);
    }
}
