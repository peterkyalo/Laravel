<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Safaricom Daraja M-Pesa Express (STK Push / Lipa na M-Pesa Online).
 * Docs: https://developer.safaricom.co.ke/APIs/MpesaExpressSimulate
 */
class MpesaGateway
{
    public function isConfigured(): bool
    {
        return filled(config('payments.mpesa.consumer_key'))
            && filled(config('payments.mpesa.consumer_secret'))
            && filled(config('payments.mpesa.passkey'));
    }

    protected function baseUrl(): string
    {
        $env = config('payments.mpesa.env') === 'live' ? 'live' : 'sandbox';

        return config("payments.mpesa.base_urls.$env");
    }

    /** Convert a base-currency amount to whole Kenyan Shillings (M-Pesa only accepts integers). */
    public static function toKes(float $amount): int
    {
        return (int) ceil($amount * (float) config('payments.mpesa.exchange_rate', 129));
    }

    /** Normalise Kenyan numbers (07.., 01.., +254.., 254..) to 2547XXXXXXXX / 2541XXXXXXXX. */
    public static function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (preg_match('/^0([17]\d{8})$/', $digits, $m)) {
            return '254'.$m[1];
        }
        if (preg_match('/^([17]\d{8})$/', $digits, $m)) {
            return '254'.$m[1];
        }
        if (preg_match('/^254([17]\d{8})$/', $digits, $m)) {
            return '254'.$m[1];
        }

        return null;
    }

    protected function accessToken(): string
    {
        return Cache::remember('mpesa_access_token_'.md5((string) config('payments.mpesa.consumer_key')), 3000, function () {
            $response = Http::withoutVerifying()
                ->withBasicAuth(config('payments.mpesa.consumer_key'), config('payments.mpesa.consumer_secret'))
                ->get($this->baseUrl().'/oauth/v1/generate', ['grant_type' => 'client_credentials']);

            if ($response->failed() || ! $response->json('access_token')) {
                throw new PaymentGatewayException('M-Pesa: unable to authenticate with Daraja.');
            }

            return $response->json('access_token');
        });
    }

    /** @return array{0: string, 1: string} [password, timestamp] */
    protected function credentials(): array
    {
        $timestamp = now('Africa/Nairobi')->format('YmdHis');
        $password = base64_encode(config('payments.mpesa.shortcode').config('payments.mpesa.passkey').$timestamp);

        return [$password, $timestamp];
    }

    /**
     * Send an STK push prompt to the student's phone.
     *
     * @return array{MerchantRequestID: string, CheckoutRequestID: string, CustomerMessage: string}
     */
    public function stkPush(Payment $payment, string $phone, int $amountKes, string $callbackUrl): array
    {
        [$password, $timestamp] = $this->credentials();

        $response = Http::withoutVerifying()->withToken($this->accessToken())
            ->post($this->baseUrl().'/mpesa/stkpush/v1/processrequest', [
                'BusinessShortCode' => config('payments.mpesa.shortcode'),
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => config('payments.mpesa.transaction_type'),
                'Amount' => $amountKes,
                'PartyA' => $phone,
                'PartyB' => config('payments.mpesa.shortcode'),
                'PhoneNumber' => $phone,
                'CallBackURL' => $callbackUrl,
                'AccountReference' => mb_substr(config('app.name', 'HMA'), 0, 12),
                'TransactionDesc' => mb_substr('Tuition for ' . config('app.name'), 0, 12),
            ]);

        if ($response->failed() || (string) $response->json('ResponseCode') !== '0') {
            throw new PaymentGatewayException('M-Pesa: '.($response->json('errorMessage') ?? $response->json('ResponseDescription') ?? 'STK push request failed.'));
        }

        return $response->json();
    }

    /** Query the status of an STK push (authoritative fallback when callbacks are delayed). */
    public function query(string $checkoutRequestId): array
    {
        [$password, $timestamp] = $this->credentials();

        $response = Http::withoutVerifying()->withToken($this->accessToken())
            ->post($this->baseUrl().'/mpesa/stkpushquery/v1/query', [
                'BusinessShortCode' => config('payments.mpesa.shortcode'),
                'Password' => $password,
                'Timestamp' => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId,
            ]);

        return $response->json() ?? [];
    }

    /** Register C2B Validation and Confirmation URLs with Daraja. */
    public function registerC2bUrls(string $validationUrl, string $confirmationUrl): array
    {
        $response = Http::withoutVerifying()->withToken($this->accessToken())
            ->post($this->baseUrl().'/mpesa/c2b/v1/registerurl', [
                'ShortCode' => config('payments.mpesa.shortcode'),
                'ResponseType' => 'Completed',
                'ConfirmationURL' => $confirmationUrl,
                'ValidationURL' => $validationUrl,
            ]);

        if ($response->failed()) {
            throw new PaymentGatewayException('M-Pesa: '.($response->json('errorMessage') ?? 'Failed to register C2B URLs.'));
        }

        return $response->json();
    }
}
