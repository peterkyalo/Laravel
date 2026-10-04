<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;

/**
 * Stripe Checkout (hosted payment page) via the Stripe REST API.
 * Docs: https://docs.stripe.com/api/checkout/sessions
 */
class StripeGateway
{
    public function isConfigured(): bool
    {
        return filled(config('payments.stripe.secret'));
    }

    /** Amount in the smallest currency unit (cents). */
    public static function toMinorUnits(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Create a hosted Checkout Session for the payment's amount.
     *
     * @return array{id: string, url: string}
     */
    public function createCheckoutSession(Payment $payment, string $successUrl, string $cancelUrl): array
    {
        $enrollment = $payment->enrollment;
        $course = $enrollment->course;

        $response = Http::asForm()
            ->withToken(config('payments.stripe.secret'))
            ->post(config('payments.stripe.base_url').'/checkout/sessions', [
                'mode' => 'payment',
                'success_url' => $successUrl.(str_contains($successUrl, '?') ? '&' : '?').'session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancelUrl,
                'client_reference_id' => (string) $payment->id,
                'customer_email' => $enrollment->user->email,
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => strtolower($payment->currency),
                        'unit_amount' => self::toMinorUnits((float) $payment->amount),
                        'product_data' => [
                            'name' => $course->title,
                            'description' => 'Full tuition — '.config('app.name'),
                        ],
                    ],
                ]],
                'metadata' => [
                    'payment_id' => $payment->id,
                    'enrollment_id' => $enrollment->id,
                ],
            ]);

        if ($response->failed() || ! $response->json('url')) {
            throw new PaymentGatewayException('Stripe: '.($response->json('error.message') ?? 'unable to create checkout session.'));
        }

        return ['id' => $response->json('id'), 'url' => $response->json('url')];
    }

    /** Fetch a Checkout Session to verify its payment state server-side. */
    public function retrieveSession(string $sessionId): array
    {
        $response = Http::withToken(config('payments.stripe.secret'))
            ->get(config('payments.stripe.base_url').'/checkout/sessions/'.urlencode($sessionId));

        if ($response->failed()) {
            throw new PaymentGatewayException('Stripe: '.($response->json('error.message') ?? 'unable to retrieve session.'));
        }

        return $response->json();
    }

    /**
     * Verify a webhook signature (Stripe-Signature header) and return the event.
     * Implements https://docs.stripe.com/webhooks#verify-manually
     */
    public function verifyWebhook(string $payload, ?string $signatureHeader, int $tolerance = 300): array
    {
        $secret = config('payments.stripe.webhook_secret');

        if (! $secret || ! $signatureHeader) {
            throw new PaymentGatewayException('Stripe webhook secret or signature missing.');
        }

        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $signatureHeader) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($key === 't') {
                $timestamp = (int) $value;
            } elseif ($key === 'v1') {
                $signatures[] = $value;
            }
        }

        if (! $timestamp || empty($signatures) || abs(time() - $timestamp) > $tolerance) {
            throw new PaymentGatewayException('Stripe webhook signature invalid or expired.');
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
        foreach ($signatures as $signature) {
            if (hash_equals($expected, (string) $signature)) {
                return json_decode($payload, true) ?? [];
            }
        }

        throw new PaymentGatewayException('Stripe webhook signature mismatch.');
    }

    /**
     * Create a Stripe PaymentIntent for card elements integration.
     *
     * @return array{id: string, client_secret: string}
     */
    public function createPaymentIntent(Payment $payment, array $params = []): array
    {
        $enrollment = $payment->enrollment;
        $course = $enrollment->course;

        $response = Http::asForm()
            ->withToken(config('payments.stripe.secret'))
            ->post(config('payments.stripe.base_url').'/payment_intents', array_merge([
                'amount' => self::toMinorUnits((float) $payment->amount),
                'currency' => strtolower($payment->currency),
                'description' => 'Tuition for '.$course->title,
                'receipt_email' => $enrollment->user->email,
                'metadata[payment_id]' => (string) $payment->id,
                'metadata[enrollment_id]' => (string) $enrollment->id,
                'automatic_payment_methods[enabled]' => 'true',
            ], $params));

        if ($response->failed() || ! $response->json('client_secret')) {
            throw new PaymentGatewayException('Stripe: '.($response->json('error.message') ?? 'unable to create payment intent.'));
        }

        return $response->json();
    }

    /** Retrieve a PaymentIntent by id. */
    public function retrievePaymentIntent(string $paymentIntentId): array
    {
        $response = Http::withToken(config('payments.stripe.secret'))
            ->get(config('payments.stripe.base_url').'/payment_intents/'.urlencode($paymentIntentId));

        if ($response->failed()) {
            throw new PaymentGatewayException('Stripe: '.($response->json('error.message') ?? 'unable to retrieve payment intent.'));
        }

        return $response->json();
    }
}

