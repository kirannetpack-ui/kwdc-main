<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Khalti Payment Initiation
     */
    public function initiateKhaltiPayment($amount, $invoiceNumber, $userId, $invoiceId)
    {
        if (blank(config('payment.khalti.secret_key'))) {
            Log::warning('Khalti payment initiation blocked because KHALTI_SECRET_KEY is not configured.');

            return [
                'success' => false,
                'message' => 'Payment provider is not configured. Please contact support.',
            ];
        }

        $payload = [
            'return_url' => route('payment.khalti.verify'),
            'website_url' => config('app.url'),
            'amount' => (int) round($amount * 100), // Khalti expects amount in paisa
            'purchase_order_id' => $invoiceNumber,
            'purchase_order_name' => 'Payment for Invoice #' . $invoiceNumber,
            'customer_info' => [
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'phone' => auth()->user()->phone ?? '9800000000',
            ],
        ];

        try {
            $response = $this->providerHttp()->withHeaders([
                'Authorization' => 'Key ' . config('payment.khalti.secret_key'),
                'Content-Type' => 'application/json',
            ])->post(rtrim(config('payment.khalti.base_url'), '/') . '/epayment/initiate/', $payload);

            if ($response->successful()) {
                $data = $response->json();

                if (blank($data['pidx'] ?? null) || blank($data['payment_url'] ?? null)) {
                    Log::error('Khalti payment initiation returned an incomplete response', [
                        'invoice_number' => $invoiceNumber,
                        'invoice_id' => $invoiceId,
                        'user_id' => $userId,
                    ]);

                    return [
                        'success' => false,
                        'message' => 'Payment initiation failed. Please try again.',
                    ];
                }

                return [
                    'success' => true,
                    'data' => $data,
                ];
            }

            Log::error('Khalti payment initiation failed', [
                'status' => $response->status(),
                'invoice_number' => $invoiceNumber,
                'invoice_id' => $invoiceId,
                'user_id' => $userId,
            ]);

            return [
                'success' => false,
                'message' => 'Payment initiation failed. Please try again.',
            ];

        } catch (\Exception $e) {
            Log::error('Khalti payment error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ];
        }
    }

    /**
     * Verify Khalti Payment
     */
    public function verifyKhaltiPayment($pidx)
    {
        if (blank(config('payment.khalti.secret_key'))) {
            Log::warning('Khalti payment verification blocked because KHALTI_SECRET_KEY is not configured.');

            return [
                'success' => false,
                'message' => 'Payment provider is not configured. Please contact support.',
            ];
        }

        try {
            $response = $this->providerHttp()->withHeaders([
                'Authorization' => 'Key ' . config('payment.khalti.secret_key'),
                'Content-Type' => 'application/json',
            ])->post(config('payment.khalti.verification_url'), [
                'pidx' => $pidx,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data,
                    'status' => $data['status'] ?? 'pending',
                ];
            }

            return [
                'success' => false,
                'message' => 'Payment verification failed.',
            ];

        } catch (\Exception $e) {
            Log::error('Khalti verification error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Verification failed. Please contact support.',
            ];
        }
    }

    /**
     * eSewa Payment Initiation
     */
    public function initiateEsewaPayment($amount, $invoiceNumber, $invoiceId)
    {
        if (blank(config('payment.esewa.merchant_code'))) {
            Log::warning('eSewa payment initiation blocked because ESEWA_MERCHANT_CODE is not configured.');

            return [
                'success' => false,
                'message' => 'Payment provider is not configured. Please contact support.',
            ];
        }

        $pid = (string) Str::uuid();
        
        $url = config('payment.esewa.payment_url') . '?' . http_build_query([
            'amt' => $amount,
            'pdc' => 0,
            'psc' => 0,
            'txAmt' => 0,
            'tAmt' => $amount,
            'pid' => $pid,
            'scd' => config('payment.esewa.merchant_code'),
            'su' => route('payment.esewa.success'),
            'fu' => route('payment.esewa.failure'),
        ]);

        return [
            'success' => true,
            'url' => $url,
            'pid' => $pid,
        ];
    }

    /**
     * Verify eSewa Payment
     */
    public function verifyEsewaPayment($pid, $refId, float $amount)
    {
        if (blank(config('payment.esewa.merchant_code'))) {
            Log::warning('eSewa payment verification blocked because ESEWA_MERCHANT_CODE is not configured.');

            return [
                'success' => false,
                'message' => 'Payment provider is not configured. Please contact support.',
            ];
        }

        try {
            $amount = round($amount, 2);
            $url = config('payment.esewa.verification_url');

            $response = $this->providerHttp()->asForm()->post($url, [
                'amt' => $amount,
                'pdc' => 0,
                'psc' => 0,
                'txAmt' => 0,
                'tAmt' => $amount,
                'pid' => $pid,
                'rid' => $refId,
                'scd' => config('payment.esewa.merchant_code'),
            ]);

            if ($response->successful() && stripos($response->body(), 'Success') !== false) {
                return [
                    'success' => true,
                    'data' => [
                        'pid' => $pid,
                        'refId' => $refId,
                        'amount' => $amount,
                        'provider_response' => $response->body(),
                    ],
                ];
            }

            return [
                'success' => false,
                'message' => 'Verification failed.',
            ];

        } catch (\Exception $e) {
            Log::error('eSewa verification error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Verification failed. Please contact support.',
            ];
        }
    }

    /**
     * Stripe Checkout Session Initiation
     */
    public function initiateStripePayment(float $amount, string $invoiceNumber, int $userId, int $invoiceId): array
    {
        if (blank(config('payment.stripe.secret'))) {
            Log::warning('Stripe payment initiation blocked because STRIPE_SECRET is not configured.');

            return [
                'success' => false,
                'message' => 'Payment provider is not configured. Please contact support.',
            ];
        }

        try {
            $currency = strtolower(config('payment.stripe.currency', 'npr'));
            $unitAmount = (int) round($amount * 100);

            $payload = [
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => $currency,
                            'product_data' => [
                                'name' => 'KTM-WDC Invoice #' . $invoiceNumber,
                                'description' => 'Logistics & Warehousing Settlement',
                            ],
                            'unit_amount' => $unitAmount,
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => route('payment.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.stripe.cancel'),
                'client_reference_id' => (string) $invoiceId,
                'metadata' => [
                    'invoice_id' => (string) $invoiceId,
                    'invoice_number' => $invoiceNumber,
                    'user_id' => (string) $userId,
                ],
            ];

            $response = $this->providerHttp()
                ->withToken(config('payment.stripe.secret'))
                ->asForm()
                ->post('https://api.stripe.com/v1/checkout/sessions', $payload);

            if ($response->successful()) {
                $session = $response->json();

                return [
                    'success' => true,
                    'session_id' => $session['id'],
                    'payment_url' => $session['url'],
                    'data' => $session,
                ];
            }

            Log::error('Stripe session creation failed: ' . $response->body());

            return [
                'success' => false,
                'message' => 'Stripe session creation failed.',
            ];
        } catch (\Exception $e) {
            Log::error('Stripe initiation exception: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Payment initiation failed. Please try again.',
            ];
        }
    }

    /**
     * Verify Stripe Checkout Session
     */
    public function verifyStripePayment(string $sessionId): array
    {
        if (blank(config('payment.stripe.secret'))) {
            return [
                'success' => false,
                'message' => 'Payment provider is not configured.',
            ];
        }

        try {
            $response = $this->providerHttp()
                ->withToken(config('payment.stripe.secret'))
                ->get('https://api.stripe.com/v1/checkout/sessions/' . urlencode($sessionId));

            if ($response->successful()) {
                $session = $response->json();

                if (($session['payment_status'] ?? '') === 'paid') {
                    return [
                        'success' => true,
                        'status' => 'Completed',
                        'data' => $session,
                    ];
                }

                return [
                    'success' => false,
                    'status' => $session['payment_status'] ?? 'unpaid',
                    'message' => 'Payment has not been completed.',
                ];
            }

            return [
                'success' => false,
                'message' => 'Stripe session verification failed.',
            ];
        } catch (\Exception $e) {
            Log::error('Stripe verification exception: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Verification error occurred.',
            ];
        }
    }

    private function providerHttp()
    {
        return Http::timeout(config('payment.http.timeout'))
            ->retry(
                config('payment.http.retry_times'),
                config('payment.http.retry_sleep_ms'),
                throw: false
            );
    }
}
