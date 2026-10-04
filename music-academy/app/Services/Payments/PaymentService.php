<?php

namespace App\Services\Payments;

use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        public StripeGateway $stripe,
        public PayPalGateway $paypal,
        public MpesaGateway $mpesa,
    ) {
    }

    /** Whether a gateway can be offered at checkout (configured, or simulated in non-production). */
    public function isAvailable(string $method): bool
    {
        if (! config("payments.$method.enabled", false)) {
            return false;
        }

        return match ($method) {
            'cash' => true,
            'stripe' => $this->stripe->isConfigured() || $this->simulating('stripe'),
            'paypal' => $this->paypal->isConfigured() || $this->simulating('paypal'),
            'mpesa' => $this->mpesa->isConfigured() || $this->simulating('mpesa'),
            default => false,
        };
    }

    /** True when a gateway lacks credentials and the built-in sandbox simulator should be used. */
    public function simulating(string $method): bool
    {
        if (! config('payments.simulate')) {
            return false;
        }

        return match ($method) {
            'stripe' => ! $this->stripe->isConfigured(),
            'paypal' => ! $this->paypal->isConfigured(),
            'mpesa' => ! $this->mpesa->isConfigured(),
            default => false,
        };
    }

    /**
     * Start a new payment attempt for the enrollment's full outstanding balance.
     * Any earlier abandoned attempt for the same gateway is cancelled.
     */
    public function createAttempt(Enrollment $enrollment, string $method, array $attributes = []): Payment
    {
        return DB::transaction(function () use ($enrollment, $method, $attributes) {
            $enrollment->payments()
                ->where('method', $method)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled', 'notes' => 'Superseded by a newer checkout attempt.']);

            return $enrollment->payments()->create(array_merge([
                'amount' => $enrollment->balance(),
                'currency' => config('payments.currency', 'USD'),
                'method' => $method,
                'reference' => strtoupper(($method === 'cash' ? 'CASH' : 'HMA').'-'.Str::random(8)),
                'status' => 'pending',
            ], $attributes));
        });
    }

    /**
     * Mark a payment as successfully paid after the gateway has confirmed it.
     *
     * @param  float|null  $confirmedAmount  Amount the gateway reports (base currency); null skips the check (admin/cash).
     * @return bool True if the payment is paid (now or previously).
     */
    public function markPaid(Payment $payment, ?float $confirmedAmount = null, ?string $reference = null, array $meta = [], ?int $recordedBy = null): bool
    {
        return DB::transaction(function () use ($payment, $confirmedAmount, $reference, $meta, $recordedBy) {
            /** @var Payment $locked */
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->isPaid()) {
                if ($reference && $reference !== $locked->reference && !str_starts_with($reference, 'SIM-') && !str_starts_with($reference, 'DAR-') && !str_starts_with($reference, 'ws_CO_')) {
                    $locked->reference = $reference;
                }
                $locked->mergeMeta($meta);
                if ($locked->isDirty('reference', 'meta')) {
                    $locked->save();
                    $payment->refresh();
                }
                return true; // idempotent: webhook + redirect may both arrive
            }

            if (! in_array($locked->status, ['pending', 'cancelled'], true) && $recordedBy === null) {
                return false;
            }

            if ($confirmedAmount !== null && round($confirmedAmount, 2) + 0.009 < round((float) $locked->amount, 2)) {
                Log::warning('Payment amount mismatch', ['payment' => $locked->id, 'expected' => $locked->amount, 'received' => $confirmedAmount]);
                $locked->mergeMeta(array_merge($meta, ['amount_mismatch' => $confirmedAmount]));
                $locked->update(['status' => 'failed', 'notes' => 'Gateway amount did not match the tuition due.']);

                return false;
            }

            $locked->mergeMeta($meta);
            $locked->fill([
                'status' => 'paid',
                'paid_at' => now(),
                'reference' => $reference ?: $locked->reference,
                'recorded_by' => $recordedBy ?? $locked->recorded_by,
            ])->save();

            $enrollment = $locked->enrollment()->with('course')->first();

            if ($enrollment->activate()) {
                // Fully paid: cancel any other dangling online attempts for this enrollment.
                $enrollment->payments()
                    ->where('id', '!=', $locked->id)
                    ->where('status', 'pending')
                    ->whereIn('method', Payment::ONLINE_METHODS)
                    ->update(['status' => 'cancelled', 'notes' => 'Tuition settled by another payment.']);
            }

            $payment->refresh();

            return true;
        });
    }

    /** Record a failed / cancelled attempt without touching the enrollment. */
    public function markFailed(Payment $payment, string $reason, string $status = 'failed', array $meta = []): void
    {
        if ($payment->isPaid()) {
            return;
        }

        $payment->mergeMeta($meta);
        $payment->fill(['status' => $status, 'notes' => $reason])->save();
    }
}
