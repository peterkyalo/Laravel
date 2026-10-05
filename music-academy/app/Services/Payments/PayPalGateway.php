<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * PayPal Orders v2 (redirect approval + server-side capture).
 * Docs: https://developer.paypal.com/docs/api/orders/v2/
 */
class PayPalGateway
{
    public function isConfigured(): bool
    {
        return filled(config('payments.paypal.client_id')) && filled(config('payments.paypal.secret'));
    }

    protected function baseUrl(): string
    {
        $mode = config('payments.paypal.mode') === 'live' ? 'live' : 'sandbox';

        return config("payments.paypal.base_urls.$mode");
    }

    protected function accessToken(): string
    {
        return Cache::remember('paypal_access_token_'.md5((string) config('payments.paypal.client_id')), 300, function () {
            $response = Http::asForm()
                ->withBasicAuth(config('payments.paypal.client_id'), config('payments.paypal.secret'))
                ->post($this->baseUrl().'/v1/oauth2/token', ['grant_type' => 'client_credentials']);

            if ($response->failed() || ! $response->json('access_token')) {
                throw new PaymentGatewayException('PayPal: unable to authenticate ('.($response->json('error_description') ?? 'unknown error').').');
            }

            return $response->json('access_token');
        });
    }

    /**
     * Create an order and return the buyer approval URL.
     *
     * @return array{id: string, approve_url: string}
     */
    public function createOrder(Payment $payment, string $returnUrl, string $cancelUrl): array
    {
        $course = $payment->enrollment->course;

        $response = Http::withToken($this->accessToken())
            ->withHeaders(['PayPal-Request-Id' => 'baritone-payment-'.$payment->id.'-'.$payment->updated_at?->timestamp])
            ->post($this->baseUrl().'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => (string) $payment->id,
                    'custom_id' => (string) $payment->id,
                    'description' => mb_substr('Tuition: '.$course->title, 0, 127),
                    'amount' => [
                        'currency_code' => strtoupper($payment->currency),
                        'value' => number_format((float) $payment->amount, 2, '.', ''),
                    ],
                ]],
                'payment_source' => [
                    'paypal' => [
                        'experience_context' => [
                            'brand_name' => mb_substr(config('app.name'), 0, 127),
                            'user_action' => 'PAY_NOW',
                            'shipping_preference' => 'NO_SHIPPING',
                            'return_url' => $returnUrl,
                            'cancel_url' => $cancelUrl,
                        ],
                    ],
                ],
            ]);

        $approve = collect($response->json('links', []))
            ->first(fn ($link) => in_array($link['rel'] ?? '', ['payer-action', 'approve'], true));

        if ($response->failed() || ! $approve) {
            throw new PaymentGatewayException('PayPal: '.($response->json('message') ?? 'unable to create order.'));
        }

        return ['id' => $response->json('id'), 'approve_url' => $approve['href']];
    }

    /** Capture an approved order. Returns the full order payload. */
    public function captureOrder(string $orderId): array
    {
        $response = Http::withToken($this->accessToken())
            ->withBody('{}', 'application/json')
            ->post($this->baseUrl().'/v2/checkout/orders/'.urlencode($orderId).'/capture');

        // ORDER_ALREADY_CAPTURED -> fetch the order so the caller can still verify it.
        if ($response->status() === 422 && collect($response->json('details', []))->contains('issue', 'ORDER_ALREADY_CAPTURED')) {
            return Http::withToken($this->accessToken())
                ->get($this->baseUrl().'/v2/checkout/orders/'.urlencode($orderId))
                ->json();
        }

        if ($response->failed()) {
            throw new PaymentGatewayException('PayPal: '.($response->json('message') ?? 'unable to capture order.'));
        }

        return $response->json();
    }
}
