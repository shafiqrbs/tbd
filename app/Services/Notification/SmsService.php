<?php

declare(strict_types=1);

namespace App\Services\Notification;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class SmsService
{
    public function send(string $message, string $phone, ?string $senderName = null): bool
    {
        try {
            $payload = [
                'apikey'         => config('sms.api_key'),
                'secretkey'      => config('sms.secret'),
                'callerID'       => $senderName ?: config('sms.caller_id'),
                'toUser'         => $phone,
                'messageContent' => $message,
            ];

            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post(config('sms.url'), $payload);

            if ($response->failed()) {
                Log::error('SMS API error response', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('SMS sending failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
