<?php

// Anti-pattern: Singleton que acumula 30 dependencias y actúa como god object.
// Violar SRP, DIP, ISP y OCP simultáneamente.
class GodSingleton
{
    private static ?self $instance = null;

    // God object: acumula responsabilidades sin límite
    private array $users = [];
    private array $orders = [];
    private array $vendors = [];
    private array $couriers = [];
    private array $products = [];
    private array $payments = [];
    private array $notifications = [];
    private array $discounts = [];
    private array $config = [];
    private array $logs = [];
    private array $metrics = [];
    private array $cache = [];
    private ?object $db = null;
    private ?object $mailer = null;
    private ?object $sms = null;
    private ?object $push = null;
    private ?object $whatsapp = null;
    private ?object $pdf = null;
    private ?object $csv = null;
    private ?object $excel = null;
    private ?object $wompi = null;
    private ?object $n1co = null;
    private ?object $bac = null;
    private ?object $audit = null;
    private ?object $inventory = null;
    private ?object $scheduler = null;
    private ?object $queue = null;
    private ?object $storage = null;
    private ?object $auth = null;
    private ?object $session = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    // 30+ métodos mezclando responsabilidades
    public function getUsers(): array { return $this->users; }
    public function setUsers(array $u): void { $this->users = $u; }
    public function getOrders(): array { return $this->orders; }
    public function setOrders(array $o): void { $this->orders = $o; }
    public function getVendors(): array { return $this->vendors; }
    public function getProducts(): array { return $this->products; }
    public function getPayments(): array { return $this->payments; }
    public function log(string $msg): void { $this->logs[] = $msg; error_log($msg); }
    public function getLogs(): array { return $this->logs; }
    public function incrementMetric(string $key): void { $this->metrics[$key] = ($this->metrics[$key] ?? 0) + 1; }
    public function getMetrics(): array { return $this->metrics; }
    public function setCache(string $key, $value): void { $this->cache[$key] = $value; }
    public function getCache(string $key) { return $this->cache[$key] ?? null; }
    public function sendEmail(string $to, string $subject, string $body): void { error_log("[EMAIL] $to: $subject"); }
    public function sendSms(string $phone, string $msg): void { error_log("[SMS] $phone: $msg"); }
    public function sendPush(int $userId, string $title): void { error_log("[PUSH] $userId: $title"); }
    public function processPaymentWompi(float $amount): array { return ['estado' => 'APROBADO']; }
    public function processPaymentN1co(int $cents): array { return ['status' => 'success']; }
    public function generatePdfReport(array $data): string { return '%PDF stub'; }
    public function generateCsvReport(array $data): string { return 'id,total'; }
    public function reserveStock(int $productId, int $qty): void { error_log("Reserve $productId x$qty"); }
    public function releaseStock(int $productId, int $qty): void { error_log("Release $productId x$qty"); }
    public function auditLog(string $event, array $data): void { error_log("[AUDIT] $event"); }
    public function getConfig(string $key): mixed { return $this->config[$key] ?? null; }
    public function setConfig(string $key, $value): void { $this->config[$key] = $value; }
    public function queueJob(callable $job): void { $job(); }
    public function authenticate(string $email, string $password): bool { return true; }
    public function storeFile(string $name, string $content): string { return "/tmp/$name"; }
}
