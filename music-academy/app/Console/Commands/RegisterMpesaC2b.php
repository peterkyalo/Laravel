<?php

namespace App\Console\Commands;

use App\Services\Payments\MpesaGateway;
use Illuminate\Console\Command;

class RegisterMpesaC2b extends Command
{
    protected $signature = 'mpesa:register-c2b {--domain= : Override the app domain (useful for ngrok)}';
    protected $description = 'Register M-Pesa C2B Validation and Confirmation URLs with Daraja';

    public function handle(MpesaGateway $mpesa)
    {
        if (! $mpesa->isConfigured()) {
            $this->error('M-Pesa credentials are not configured in your .env file.');
            return self::FAILURE;
        }

        $token = config('payments.mpesa.callback_token');
        $domain = $this->option('domain') ?: rtrim(config('app.url'), '/');

        $validationUrl = $domain . '/payments/mpesa/c2b/validate/' . $token;
        $confirmationUrl = $domain . '/payments/mpesa/c2b/confirm/' . $token;

        $this->info("Registering C2B URLs...");
        $this->line("Validation:   $validationUrl");
        $this->line("Confirmation: $confirmationUrl");

        try {
            $result = $mpesa->registerC2bUrls($validationUrl, $confirmationUrl);
            $this->info('Success! Response: ' . json_encode($result));
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}
