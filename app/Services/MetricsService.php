<?php
namespace App\Services;
class MetricsService
{
    public function increment(string $metric, int $by = 1): void { /* stub */ }
    public function gauge(string $metric, float $value): void { /* stub */ }
    public function timing(string $metric, int $timestamp): void { /* stub */ }
}
