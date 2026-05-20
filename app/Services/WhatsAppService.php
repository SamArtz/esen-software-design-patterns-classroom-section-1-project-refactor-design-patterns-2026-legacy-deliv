<?php
namespace App\Services;
class WhatsAppService
{
    public function send(string $phone, string $message): bool
    {
        if (empty($phone)) return false;
        \Illuminate\Support\Facades\Log::info("[WHATSAPP] To: {$phone} | Message: {$message}");
        return true;
    }
}
