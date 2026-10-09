<?php

namespace App\Services;

use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Support\Facades\Log;

class AfricasTalkingService
{
    protected ?AfricasTalking $at = null;
    protected string $senderId;

    /**
     * Whether this service is disabled (missing/invalid credentials)
     */
    protected bool $disabled = false;

    public function __construct()
    {
        try {
            $username = config('services.africastalking.username');
            $apiKey   = config('services.africastalking.api_key');
            $this->senderId = (string) config('services.africastalking.sender_id', '');

            if (empty($username) || empty($apiKey)) {
                $this->disabled = true;
                Log::warning('AfricasTalkingService: username or api_key is missing. SMS sending is disabled.');
                return;
            }

            $this->at = new AfricasTalking($username, $apiKey);
        } catch (\Throwable $e) {
            $this->disabled = true;
            Log::error('AfricasTalkingService: constructor failed — SMS sending is disabled.', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send SMS via Africa's Talking
     *
     * @param string $phone Phone number in format 07XXXXXXXX (Kenya)
     * @param string $message SMS message content
     * @return bool True if SMS sent successfully
     */
    public function sendSms(string $phone, string $message): bool
    {
        if ($this->disabled || $this->at === null) {
            Log::warning('AfricasTalkingService: sendSms called but service is disabled.', [
                'phone' => $phone,
            ]);
            return false;
        }

        try {
            // Convert 07XXXXXXXX to +254XXXXXXXX
            $formattedPhone = $this->formatPhoneNumber($phone);

            $sms    = $this->at->sms();
            $result = $sms->send([
                'to'      => $formattedPhone,
                'message' => $message,
                'from'    => $this->senderId,
            ]);

            // Check if message was sent successfully
            if (isset($result['data']['SMSMessageData']['Recipients'][0]['status']) &&
                $result['data']['SMSMessageData']['Recipients'][0]['status'] === 'Success') {
                Log::info('SMS sent successfully', [
                    'phone' => $phone,
                    'cost'  => $result['data']['SMSMessageData']['Recipients'][0]['cost'] ?? null,
                ]);
                return true;
            }

            Log::warning('SMS sending failed', [
                'phone'  => $phone,
                'result' => $result,
            ]);
            return false;

        } catch (\Throwable $e) {
            Log::error('SMS exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Format phone number from 07XXXXXXXX to +254XXXXXXXX
     *
     * @param string $phone
     * @return string
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove leading zero and add +254
        if (str_starts_with($phone, '0')) {
            return '+254' . substr($phone, 1);
        }
        return $phone;
    }
}
