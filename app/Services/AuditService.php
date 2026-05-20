<?php
namespace App\Services;
class AuditService
{
    public function log(string $event, int $entityId, array $data = []): void
    {
        \Illuminate\Support\Facades\Log::channel('legacy')->info("[AUDIT] {$event} #{$entityId}", $data);
    }
}
