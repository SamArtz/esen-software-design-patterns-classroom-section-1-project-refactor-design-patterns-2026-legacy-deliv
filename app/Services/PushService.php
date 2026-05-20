<?php
namespace App\Services;
class PushService
{
    public function send(int $userId, string $title, string $body): bool
    {
        \Illuminate\Support\Facades\Log::info("[PUSH] UserId: {$userId} | Title: {$title}");
        return true;
    }
}
