<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayHeroService
{
    protected string $basicAuth;
    protected string $channelId;
    protected ?string $webhookSecret;
    protected string $baseUrl = 'https://backend.payhero.co.ke/api/v2';

    public function __construct()
    {
        $this->basicAuth     = config('services.payhero.basic_auth');
        $this->channelId     = (string) config('services.payhero.channel_id');
        $this->webhookSecret = config('services.payhero.webhook_secret');
    }

    /**
     * Initiate STK Push payment
     *
     * @param string $phone     Phone in format 07XXXXXXXX
     * @param float  $amount    Amount in KSh
     * @param string $reference Unique reference (payment ID or session token)
     * @return array ['success' => bool, 'reference' => string|null, 'message' => string, 'data' => array]
     */
    public function initiateStk(string $phone, float $amount, string $reference): array
    {
        // Convert 07XXXXXXXX to 2547XXXXXXXX
        $formattedPhone = $this->formatPhone($phone);

        $payload = [
            'amount'       => (int) round($amount),
            'phone_number' => $formattedPhone,
            'channel_id'   => (int) $this->channelId,
            'provider'     => 'm-pesa',
            'external_reference' => $reference,
            'callback_url' => route('payment.webhook'),
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->basicAuth,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->post($this->baseUrl . '/payments', $payload);

            $data = $response->json();

            Log::info('PayHero STK Push', [
                'reference' => $reference,
                'phone' => $phone,
                'amount' => $amount,
                'status' => $response->status(),
                'response' => $data,
            ]);

            if ($response->successful() && isset($data['reference'])) {
                return [
                    'success'   => true,
                    'reference' => $data['reference'],
                    'message'   => 'STK Push initiated. Check your phone.',
                    'data'      => $data,
                ];
            }

            $message = $data['message'] ?? $data['error'] ?? 'Payment initiation failed.';

            return [
                'success'   => false,
                'reference' => null,
                'message'   => $message,
                'data'      => $data,
            ];

        } catch (\Exception $e) {
            Log::error('PayHero STK Push exception', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return [
                'success'   => false,
                'reference' => null,
                'message'   => 'Payment service unavailable. Please try again.',
                'data'      => [],
            ];
        }
    }

    /**
     * Verify webhook signature
     *
     * PayHero sends a X-PAYHERO-SIGNATURE header with HMAC-SHA256 of the raw body.
     *
     * @param Request $request
     * @return bool
     */
    public function verifySignature(Request $request): bool
    {
        // If no webhook secret configured, skip verification
        if (!$this->webhookSecret) {
            Log::warning('PayHero webhook: No webhook secret configured, skipping signature verification.');
            return true;
        }

        $signature = $request->header('X-PAYHERO-SIGNATURE');
        if (!$signature) {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $this->webhookSecret);

        return hash_equals($expected, $signature);
    }

    /**
     * Format phone from 07XXXXXXXX to 2547XXXXXXXX
     */
    protected function formatPhone(string $phone): string
    {
        if (str_starts_with($phone, '0')) {
            return '254' . substr($phone, 1);
        }
        if (str_starts_with($phone, '+254')) {
            return ltrim($phone, '+');
        }
        return $phone;
    }
}
