<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $provider;
    protected bool $enabled;

    public function __construct()
    {
        $this->provider = config('services.sms.provider', 'log');
        $this->enabled = config('services.sms.enabled', false);
    }

    public function send(string $phoneNumber, string $message): bool
    {
        if (!$this->enabled) {
            Log::info('[SMS-DISABLED] Delivery skipped because SMS is disabled.');
            return true;
        }

        return match ($this->provider) {
            'semaphore' => $this->sendViaSemaphore($phoneNumber, $message),
            'twilio' => $this->sendViaTwilio($phoneNumber, $message),
            default => $this->sendViaLog($phoneNumber, $message),
        };
    }

    protected function sendViaLog(string $phoneNumber, string $message): bool
    {
        Log::channel('single')->info('[SMS] Simulated delivery completed.');
        return true;
    }

    protected function sendViaSemaphore(string $phoneNumber, string $message): bool
    {
        try {
            $apiKey = config('services.sms.semaphore.api_key');
            $senderName = config('services.sms.semaphore.sender_name', 'SEMAPHORE');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.semaphore.co/api/v4/messages');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'apikey' => $apiKey,
                'number' => $phoneNumber,
                'message' => $message,
                'sendername' => $senderName,
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            Log::info('[SMS-SEMAPHORE] Delivery request completed.');
            return true;
        } catch (\Exception $e) {
            Log::error('[SMS-SEMAPHORE] Delivery failed.', ['exception' => $e::class]);
            return false;
        }
    }

    protected function sendViaTwilio(string $phoneNumber, string $message): bool
    {
        try {
            $sid = config('services.sms.twilio.sid');
            $token = config('services.sms.twilio.token');
            $from = config('services.sms.twilio.from');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.twilio.com/2010-04-01/Accounts/' . $sid . '/Messages.json');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'To' => $phoneNumber,
                'From' => $from,
                'Body' => $message,
            ]));
            curl_setopt($ch, CURLOPT_USERPWD, $sid . ':' . $token);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            Log::info('[SMS-TWILIO] Delivery request completed.');
            return true;
        } catch (\Exception $e) {
            Log::error('[SMS-TWILIO] Delivery failed.', ['exception' => $e::class]);
            return false;
        }
    }
}