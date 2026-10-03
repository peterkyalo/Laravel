<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Currency
    |--------------------------------------------------------------------------
    | Course fees are stored in this currency. Stripe and PayPal charge in it
    | directly; M-Pesa charges in KES using the exchange rate below.
    */
    'currency' => env('PAYMENT_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Sandbox Simulation
    |--------------------------------------------------------------------------
    | When a gateway has no API credentials configured and simulation is
    | enabled, checkout routes to a built-in test gateway page instead of the
    | real provider. NEVER enable this in production.
    */
    'simulate' => (bool) env('PAYMENT_SIMULATE', env('APP_ENV', 'production') !== 'production'),

    'stripe' => [
        'enabled' => (bool) env('STRIPE_ENABLED', true),
        'secret' => env('STRIPE_SECRET'),
        'public' => env('STRIPE_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'base_url' => 'https://api.stripe.com/v1',
    ],

    'paypal' => [
        'enabled' => (bool) env('PAYPAL_ENABLED', true),
        'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox | live
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        'base_urls' => [
            'sandbox' => 'https://api-m.sandbox.paypal.com',
            'live' => 'https://api-m.paypal.com',
        ],
    ],

    'mpesa' => [
        'enabled' => (bool) env('MPESA_ENABLED', true),
        'env' => env('MPESA_ENV', 'sandbox'), // sandbox | live
        'consumer_key' => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'shortcode' => env('MPESA_SHORTCODE', '174379'),
        'passkey' => env('MPESA_PASSKEY'),
        'transaction_type' => env('MPESA_TRANSACTION_TYPE', 'CustomerPayBillOnline'),
        // Secret path token appended to the callback URL (Daraja callbacks are unsigned).
        'callback_token' => env('MPESA_CALLBACK_TOKEN', substr(hash('sha256', (string) env('APP_KEY')), 0, 32)),
        // Optional public override (e.g. an ngrok URL) — must be HTTPS for Daraja.
        'callback_url' => env('MPESA_CALLBACK_URL'),
        // 1 unit of base currency => N Kenyan Shillings.
        'exchange_rate' => (float) env('MPESA_EXCHANGE_RATE', 129),
        'base_urls' => [
            'sandbox' => 'https://sandbox.safaricom.co.ke',
            'live' => 'https://api.safaricom.co.ke',
        ],
    ],

    'cash' => [
        'enabled' => (bool) env('CASH_PAYMENTS_ENABLED', true),
        'instructions' => env(
            'CASH_PAYMENT_INSTRUCTIONS',
            'Visit the Harmonia bursar office (Mon–Fri, 9:00–17:00) with your payment reference. Your classroom unlocks as soon as the bursar confirms receipt of the full tuition.'
        ),
    ],
];
