<?php
namespace App\Services;
class EmailService
{
    public function send(string $to, string $subject, string $body): bool
    {
        if (empty($to)) return false;
        \Illuminate\Support\Facades\Log::info("[EMAIL] To: {$to} | Subject: {$subject}");
        return true;
    }
}
