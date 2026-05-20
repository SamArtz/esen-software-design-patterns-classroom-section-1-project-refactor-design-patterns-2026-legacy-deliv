<?php

// Anti-pattern: Observer pattern con un único observer hardcoded dentro del Subject.
// No añade nada vs. una llamada directa.
// En S8 se muestra el Observer real con múltiples observadores dinámicos.

interface Observer
{
    public function update(string $event, array $data): void;
}

class OrderLogger implements Observer
{
    public function update(string $event, array $data): void
    {
        error_log("[ORDER_LOG] Event: $event | " . json_encode($data));
    }
}

class OrderSubject
{
    // Anti-pattern: observer único hardcodeado. No hay lista de observers.
    private Observer $observer;

    public function __construct()
    {
        // Hardcoded en el constructor - no se puede cambiar ni agregar más
        $this->observer = new OrderLogger();
    }

    public function changeStatus(string $newStatus): void
    {
        // ... lógica de negocio ...
        // Notifica al único observer hardcodeado
        $this->observer->update('status_changed', ['new_status' => $newStatus]);
    }
}
