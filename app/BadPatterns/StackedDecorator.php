<?php

namespace App\BadPatterns;

/**
 * ANTI-PATRÓN: Decorator apilado en exceso
 *
 * Problema: Decorator es poderoso cuando se apilan 2-3 capas con propósito claro.
 * Cuando se apilan 6+, el objeto resultante es opaco, difícil de debuggear,
 * y el orden de aplicación se vuelve crítico y frágil.
 *
 * Contexto en el legacy: el módulo de notificaciones tiene flags (is_encrypted,
 * is_signed, is_logged) que en S8 L se refactorizan con Decorator.
 * Este archivo muestra qué pasa cuando se abusa de ese refactor:
 * se termina construyendo una cadena de 6 decoradores para enviar un email.
 *
 * Señal de alerta: cuando necesitas un comentario para explicar
 * en qué orden se apilan los decoradores, hay demasiados.
 */
interface Notifier
{
    public function send(string $recipient, string $message): string;
}

class BaseEmailNotifier implements Notifier
{
    public function send(string $recipient, string $message): string
    {
        return "EMAIL to {$recipient}: {$message}";
    }
}

class LoggingDecorator implements Notifier
{
    public function __construct(private Notifier $inner) {}

    public function send(string $recipient, string $message): string
    {
        $result = $this->inner->send($recipient, $message);
        \App\Support\Logger::getInstance()->log("Notification sent to {$recipient}");
        return $result;
    }
}

class EncryptionDecorator implements Notifier
{
    public function __construct(private Notifier $inner) {}

    public function send(string $recipient, string $message): string
    {
        $encrypted = base64_encode($message);
        return $this->inner->send($recipient, $encrypted);
    }
}

class SignatureDecorator implements Notifier
{
    public function __construct(private Notifier $inner) {}

    public function send(string $recipient, string $message): string
    {
        $signed = $message . "\n-- Sistema Legacy Delivery";
        return $this->inner->send($recipient, $signed);
    }
}

class RetryDecorator implements Notifier
{
    public function __construct(private Notifier $inner, private int $maxRetries = 3) {}

    public function send(string $recipient, string $message): string
    {
        $attempts = 0;
        while ($attempts < $this->maxRetries) {
            try {
                return $this->inner->send($recipient, $message);
            } catch (\Exception $e) {
                $attempts++;
                if ($attempts >= $this->maxRetries) throw $e;
            }
        }
        return '';
    }
}

class RateLimitDecorator implements Notifier
{
    private static array $sentAt = [];

    public function __construct(private Notifier $inner, private int $maxPerMinute = 10) {}

    public function send(string $recipient, string $message): string
    {
        $now = time();
        self::$sentAt = array_filter(self::$sentAt, fn($t) => $now - $t < 60);
        if (count(self::$sentAt) >= $this->maxPerMinute) {
            throw new \RuntimeException('Rate limit exceeded.');
        }
        self::$sentAt[] = $now;
        return $this->inner->send($recipient, $message);
    }
}

class AuditDecorator implements Notifier
{
    public function __construct(private Notifier $inner) {}

    public function send(string $recipient, string $message): string
    {
        $result = $this->inner->send($recipient, $message);
        \App\Support\Logger::getInstance()->log("AUDIT: notification to {$recipient} at " . now());
        return $result;
    }
}

// El resultado: para enviar un email, construís esto:
//
// $notifier = new AuditDecorator(
//     new RateLimitDecorator(
//         new RetryDecorator(
//             new LoggingDecorator(
//                 new SignatureDecorator(
//                     new EncryptionDecorator(
//                         new BaseEmailNotifier()
//                     )
//                 )
//             ),
//             maxRetries: 3
//         ),
//         maxPerMinute: 10
//     )
// );
//
// ¿En qué orden se aplica el cifrado y la firma? ¿El log ve el mensaje cifrado o el original?
// ¿El audit log y el logging log son redundantes? Nadie lo sabe sin leer las 6 clases.
