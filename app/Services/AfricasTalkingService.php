<?php

namespace App\Services;

use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Support\Facades\Log;

class AfricasTalkingService
{
    protected AfricasTalking $at;
    protected string $senderId;

    public function __construct()
    {
        $username = config('services.africastalking.username');
        $apiKey = config('services.africastalking.api_key');
        $this->senderId = config('services.africastalking.sender_id');

        $this->at = new AfricasTalking($username, $apiKey);
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
        try {
            // Convert 07XXXXXXXX to +254XXXXXXXX
            $formattedPhone = $this->formatPhoneNumber($phone);

            $sms = $this->at->sms();
            $result = $sms->send([
                'to' => $formattedPhone,
                'message' => $message,
                'from' => $this->senderId,
            ]);

            // Check if message was sent successfully
            if (isset($result['data']['SMSMessageData']['Recipients'][0]['status']) &&
                $result['data']['SMSMessageData']['Recipients'][0]['status'] === 'Success') {
                Log::info('SMS sent successfully', [
                    'phone' => $phone,
                    'cost' => $result['data']['SMSMessageData']['Recipients'][0]['cost'] ?? null,
                ]);
                return true;
            }

            Log::warning('SMS sending failed', [
                'phone' => $phone,
                'result' => $result,
            ]);
            return false;

        } catch (\Exception $e) {
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
