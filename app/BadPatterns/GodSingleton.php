<?php

namespace App\BadPatterns;

/**
 * ANTI-PATRÓN: Singleton God Object
 *
 * Problema: El Singleton se convierte en un contenedor global de estado
 * que acumula responsabilidades sin límite. Cualquier parte del sistema
 * puede escribir y leer de él, creando acoplamiento invisible entre módulos.
 *
 * El Logger del legacy (app/Support/Logger.php) ya es un Singleton simple.
 * Este archivo muestra a dónde llega cuando nadie pone límites:
 * configuración, caché, sesión, métricas y logs — todo en uno.
 *
 * Señal de alerta: cuando el Singleton tiene más de 2-3 responsabilidades,
 * o cuando su getInstance() aparece en más de 5 archivos distintos,
 * ya es un God Object.
 */
class AppState
{
    private static ?self $instance = null;

    // Acumula todo porque "ya estamos aquí de todas formas"
    private array $logs         = [];
    private array $cache        = [];
    private array $config       = [];
    private array $sessionData  = [];
    private array $metrics      = [];
    private array $featureFlags = [];
    private array $activeUsers  = [];

    private function __construct()
    {
        $this->config = [
            'app_name'    => 'Legacy Delivery',
            'max_retries' => 3,
            'timeout'     => 30,
        ];
    }

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    // Responsabilidad 1: logging
    public function log(string $message, string $level = 'info'): void
    {
        $this->logs[] = ['ts' => now(), 'level' => $level, 'msg' => $message];
    }

    public function getLogs(): array { return $this->logs; }

    // Responsabilidad 2: caché en memoria
    public function cacheSet(string $key, mixed $value, int $ttl = 300): void
    {
        $this->cache[$key] = ['value' => $value, 'expires_at' => time() + $ttl];
    }

    public function cacheGet(string $key): mixed
    {
        if (!isset($this->cache[$key])) return null;
        if (time() > $this->cache[$key]['expires_at']) {
            unset($this->cache[$key]);
            return null;
        }
        return $this->cache[$key]['value'];
    }

    // Responsabilidad 3: configuración global
    public function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function setConfig(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }

    // Responsabilidad 4: sesión del usuario actual
    public function setSession(string $key, mixed $value): void
    {
        $this->sessionData[$key] = $value;
    }

    public function getSession(string $key): mixed
    {
        return $this->sessionData[$key] ?? null;
    }

    // Responsabilidad 5: métricas
    public function increment(string $metric): void
    {
        $this->metrics[$metric] = ($this->metrics[$metric] ?? 0) + 1;
    }

    public function getMetrics(): array { return $this->metrics; }

    // Responsabilidad 6: feature flags
    public function isEnabled(string $flag): bool
    {
        return $this->featureFlags[$flag] ?? false;
    }

    public function enableFlag(string $flag): void
    {
        $this->featureFlags[$flag] = true;
    }

    // Responsabilidad 7: usuarios activos
    public function registerActiveUser(int $userId): void
    {
        $this->activeUsers[$userId] = now();
    }

    public function getActiveUsers(): array { return $this->activeUsers; }

    private function __clone() {}
    public function __wakeup(): void { throw new \Exception('Cannot unserialize singleton.'); }
}

// El resultado: cualquier clase del sistema puede hacer esto:
// AppState::getInstance()->log('...');
// AppState::getInstance()->cacheSet('orders', $data);
// AppState::getInstance()->increment('orders.created');
// AppState::getInstance()->setSession('current_vendor', $id);
//
// Nadie sabe qué depende de qué. Imposible de testear. Imposible de reemplazar.
