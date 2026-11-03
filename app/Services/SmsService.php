<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * In-memory sent messages for test assertions.
     */
    public static array $sentMessages = [];

    /**
     * Send an SMS message to a recipient.
     *
     * @param string $phone
     * @param string $message
     * @param string $purpose
     * @return array
     */
    public function send(string $phone, string $message, string $purpose = 'notification'): array
    {
        $normalizedPhone = $this->normalizePhoneNumber($phone);

        if (!$normalizedPhone) {
            Log::warning("SmsService: Invalid Nepal phone number skipped: {$phone}");
            return [
                'success' => false,
                'error' => 'Invalid Nepal mobile number format. Must be 10 digits starting with 98 or 97.',
            ];
        }

        $driver = config('sms.default', 'log');

        self::$sentMessages[] = [
            'to' => $normalizedPhone,
            'message' => $message,
            'purpose' => $purpose,
            'driver' => $driver,
            'timestamp' => now()->toIso8601String(),
        ];

        if ($driver === 'sparrow') {
            return $this->sendViaSparrow($normalizedPhone, $message);
        }

        if ($driver === 'aakash') {
            return $this->sendViaAakash($normalizedPhone, $message);
        }

        // Default 'log' driver for local development and test harness
        Log::info("📱 [SMS {$purpose}] To: {$normalizedPhone} | Message: {$message}");

        return [
            'success' => true,
            'driver' => 'log',
            'to' => $normalizedPhone,
            'message' => $message,
        ];
    }

    /**
     * Normalize a phone number to standard 10-digit Nepal mobile format (98XXXXXXXX or 97XXXXXXXX).
     */
    public function normalizePhoneNumber(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Remove all non-numeric characters
        $digits = preg_replace('/\D/', '', $phone);

        // Strip leading 977 country code if present
        if (str_starts_with($digits, '977') && strlen($digits) === 13) {
            $digits = substr($digits, 3);
        }

        // Must be exactly 10 digits starting with 98 or 97 or 96
        if (strlen($digits) === 10 && in_array(substr($digits, 0, 2), ['98', '97', '96'])) {
            return $digits;
        }

        return null;
    }

    /**
     * Send SMS via Sparrow SMS API.
     */
    protected function sendViaSparrow(string $to, string $message): array
    {
        $config = config('sms.providers.sparrow');

        try {
            $response = Http::timeout(10)->post($config['endpoint'], [
                'token' => $config['token'],
                'from' => $config['from'],
                'to' => $to,
                'text' => $message,
            ]);

            if ($response->successful()) {
                return ['success' => true, 'response' => $response->json()];
            }

            Log::error('Sparrow SMS failure: ' . $response->body());
            return ['success' => false, 'error' => $response->body()];
        } catch (\Throwable $e) {
            Log::error('Sparrow SMS exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send SMS via Aakash SMS API.
     */
    protected function sendViaAakash(string $to, string $message): array
    {
        $config = config('sms.providers.aakash');

        try {
            $response = Http::timeout(10)->post($config['endpoint'], [
                'auth_token' => $config['auth_token'],
                'to' => $to,
                'text' => $message,
            ]);

            if ($response->successful()) {
                return ['success' => true, 'response' => $response->json()];
            }

            Log::error('Aakash SMS failure: ' . $response->body());
            return ['success' => false, 'error' => $response->body()];
        } catch (\Throwable $e) {
            Log::error('Aakash SMS exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send a 2FA or verification OTP code.
     */
    public function sendOtp(string $phone, string $otp): array
    {
        $msg = "Your KTM-WDC security verification code is {$otp}. Valid for 10 minutes. Do not share this code.";
        return $this->send($phone, $msg, 'otp');
    }

    /**
     * Send dispatch job assignment notification to commercial driver.
     */
    public function notifyDriverAssigned(string $phone, string $dispatchNumber, string $pickup, string $delivery, float $fare): array
    {
        $fareNpr = number_format($fare);
        $msg = "KTM-WDC Job #{$dispatchNumber}: Pickup from {$pickup} to {$delivery}. Payout: NPR {$fareNpr}. Open app to start trip.";
        return $this->send($phone, $msg, 'dispatch_assignment');
    }

    /**
     * Send delivery handoff OTP to consignee.
     */
    public function notifyConsigneeDeliveryOtp(string $phone, string $dispatchNumber, string $otp): array
    {
        $msg = "KTM-WDC Consignment #{$dispatchNumber} is out for delivery. Share OTP {$otp} with driver upon physical cargo verification.";
        return $this->send($phone, $msg, 'delivery_otp');
    }

    /**
     * Send payment clearance notification to client.
     */
    public function notifyPaymentCleared(string $phone, string $invoiceNumber, float $amount): array
    {
        $amountNpr = number_format($amount, 2);
        $msg = "KTM-WDC: Payment of NPR {$amountNpr} for Invoice #{$invoiceNumber} has been verified and cleared. Tax receipt is available.";
        return $this->send($phone, $msg, 'payment_clearance');
    }
}
