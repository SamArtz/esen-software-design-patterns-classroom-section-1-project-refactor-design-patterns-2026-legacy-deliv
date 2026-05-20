<?php

// Anti-pattern: Decorator apilado 6 veces sin razón de negocio.
// Muestra cuándo el Decorator se vuelve sobre-ingeniería.
// En S5 L se discute cuándo usar vs no usar Decorator.

interface NotificationInterface
{
    public function send(string $to, string $message): bool;
}

class BaseNotification implements NotificationInterface
{
    public function send(string $to, string $message): bool
    {
        error_log("[BASE] To: $to | $message");
        return true;
    }
}

abstract class NotificationDecorator implements NotificationInterface
{
    public function __construct(protected NotificationInterface $wrapped) {}
}

class EncryptedDecorator extends NotificationDecorator
{
    public function send(string $to, string $message): bool
    {
        return $this->wrapped->send($to, base64_encode($message));
    }
}

class LoggedDecorator extends NotificationDecorator
{
    public function send(string $to, string $message): bool
    {
        error_log("[LOGGED] Sending to $to");
        return $this->wrapped->send($to, $message);
    }
}

class SignedDecorator extends NotificationDecorator
{
    public function send(string $to, string $message): bool
    {
        return $this->wrapped->send($to, $message . "\n--Signed");
    }
}

class CompressedDecorator extends NotificationDecorator
{
    public function send(string $to, string $message): bool
    {
        return $this->wrapped->send($to, gzcompress($message) ?: $message);
    }
}

class TrackedDecorator extends NotificationDecorator
{
    private static array $sent = [];
    public function send(string $to, string $message): bool
    {
        self::$sent[] = ['to' => $to, 'at' => time()];
        return $this->wrapped->send($to, $message);
    }
}

class CachedDecorator extends NotificationDecorator
{
    private static array $cache = [];
    public function send(string $to, string $message): bool
    {
        $key = md5($to . $message);
        if (isset(self::$cache[$key])) return true; // no re-envía
        self::$cache[$key] = true;
        return $this->wrapped->send($to, $message);
    }
}

// Anti-pattern: apilado 6 veces sin razón clara
$overDecorated = new EncryptedDecorator(
    new LoggedDecorator(
        new SignedDecorator(
            new CompressedDecorator(
                new TrackedDecorator(
                    new CachedDecorator(
                        new BaseNotification()
                    )
                )
            )
        )
    )
);
