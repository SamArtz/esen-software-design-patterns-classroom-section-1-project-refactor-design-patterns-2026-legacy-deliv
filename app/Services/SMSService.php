<?php
namespace App\Services;
class SMSService
{
    public function send(string $phone, string $message): bool
    {
        if (empty($phone)) return false;
        \Illuminate\Support\Facades\Log::info("[SMS] To: {$phone} | Message: {$message}");
        return true;
    }
}
