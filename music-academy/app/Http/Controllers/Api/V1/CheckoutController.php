<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Services\Payments\MpesaGateway;
use App\Services\Payments\PaymentGatewayException;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(
        protected PaymentService $payments
    ) {
    }

    /**
     * Get available payment methods and course pricing summary for checkout.
     */
    public function summary(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();
        $enrollment = $user->enrollments()->where('course_id', $course->id)->first();
        $balance = $enrollment ? $enrollment->balance() : (float) $course->fee;

        $methods = collect(Payment::METHODS)
            ->keys()
            ->filter(fn ($m) => $this->payments->isAvailable($m))
            ->values();

        return response()->json([
            'course_id' => $course->id,
            'course_title' => $course->title,
            'slug' => $course->slug,
            'price' => (float) $course->fee,
            'balance_due' => (float) $balance,
            'is_fully_paid' => $enrollment ? $enrollment->isFullyPaid() : false,
            'available_methods' => $methods,
        ]);
    }

    /**
     * Initiate M-Pesa STK Push.
     */
    public function mpesaStkPush(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => ['required', 'integer'],
            'phone' => ['required', 'string'],
        ]);

        $course = Course::findOrFail($request->integer('course_id'));
        $user = $request->user();

        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['status' => 'pending', 'progress' => 0, 'enrolled_at' => now()]
        );

        $phone = preg_replace('/[^\d]/', '', $request->input('phone'));
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '7') || str_starts_with($phone, '1')) {
            $phone = '254' . $phone;
        }

        if (! preg_match('/^254[17]\d{8}$/', $phone)) {
            return response()->json([
                'success' => false,
                'error' => 'Please enter a valid Safaricom phone number (e.g. 0712 345 678 or +254 712 345 678).',
            ], 422);
        }

        $kes = MpesaGateway::toKes($enrollment->balance());
        $payment = $this->payments->createAttempt($enrollment, 'mpesa', [
            'meta' => [
                'phone' => $phone,
                'amount_kes' => $kes,
                'exchange_rate' => (float) config('payments.mpesa.exchange_rate', 130),
            ],
        ]);

        if ($this->payments->simulating('mpesa') || ! $this->payments->mpesa->isConfigured()) {
            $simCheckoutId = 'ws_SIM_' . strtoupper(Str::random(12));
            $payment->update([
                'gateway_reference' => $simCheckoutId,
                'meta' => array_merge($payment->meta ?? [], ['simulated' => true]),
            ]);

            Cache::put('mpesa_sim_' . $payment->id, now()->addSeconds(6)->timestamp, now()->addMinutes(10));

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'checkout_request_id' => $simCheckoutId,
                'phone' => $phone,
                'amount_kes' => $kes,
                'simulated' => true,
                'message' => 'STK Push sent to device (Simulator mode).',
            ]);
        }

        try {
            $callbackUrl = url('/api/v1/checkout/mpesa/callback/' . config('payments.mpesa.callback_token'));
            $res = $this->payments->mpesa->stkPush(
                $payment,
                $phone,
                (int) $kes,
                $callbackUrl
            );

            $checkoutId = $res['CheckoutRequestID'] ?? null;
            $payment->update(['gateway_reference' => $checkoutId]);

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'checkout_request_id' => $checkoutId,
                'phone' => $phone,
                'amount_kes' => $kes,
                'simulated' => false,
                'message' => $res['CustomerMessage'] ?? 'STK Push sent successfully.',
            ]);
        } catch (PaymentGatewayException $e) {
            Log::error('M-Pesa STK Push error', ['payment' => $payment->id, 'error' => $e->getMessage()]);
            $this->payments->markFailed($payment, $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Query M-Pesa transaction status.
     */
    public function mpesaQuery(Request $request): JsonResponse
    {
        $request->validate([
            'payment_id' => ['required', 'integer'],
        ]);

        $payment = Payment::with('enrollment.course')->where('payment_method', 'mpesa')->findOrFail($request->integer('payment_id'));

        if ($payment->status === 'completed') {
            return response()->json([
                'status' => 'paid',
                'paid' => true,
                'result_code' => 0,
                'result_desc' => 'Payment completed successfully.',
            ]);
        }

        if ($payment->meta['simulated'] ?? false) {
            $unlockAt = Cache::get('mpesa_sim_' . $payment->id);
            if ($unlockAt && now()->timestamp >= (int) $unlockAt) {
                $receipt = 'SIM' . strtoupper(Str::random(8));
                $this->payments->markPaid($payment, (float) $payment->amount, $receipt, ['simulated' => true]);

                return response()->json([
                    'status' => 'paid',
                    'paid' => true,
                    'result_code' => 0,
                    'result_desc' => 'Simulated payment succeeded.',
                ]);
            }

            return response()->json([
                'status' => 'pending',
                'paid' => false,
                'result_code' => 4999,
                'result_desc' => 'Awaiting M-Pesa PIN entry on device.',
            ]);
        }

        return response()->json([
            'status' => 'pending',
            'paid' => false,
            'result_code' => -1,
            'result_desc' => 'Awaiting user PIN entry on device.',
        ]);
    }

    /**
     * Create Stripe payment intent.
     */
    public function stripeCreateIntent(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => ['required', 'integer'],
        ]);

        $course = Course::findOrFail($request->integer('course_id'));
        $user = $request->user();

        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['status' => 'pending', 'progress' => 0, 'enrolled_at' => now()]
        );

        $payment = $this->payments->createAttempt($enrollment, 'stripe');

        if ($this->payments->simulating('stripe') || ! $this->payments->stripe->isConfigured()) {
            $simIntentId = 'pi_sim_' . Str::random(24);
            $simSecret = $simIntentId . '_secret_' . Str::random(24);
            $payment->update([
                'gateway_reference' => $simIntentId,
                'meta' => array_merge($payment->meta ?? [], ['simulated' => true]),
            ]);

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'client_secret' => $simSecret,
                'publishable_key' => config('payments.stripe.public', 'pk_test_simulated_baritone'),
                'amount' => (float) $payment->amount,
                'currency' => strtolower($payment->currency),
                'simulated' => true,
            ]);
        }

        try {
            $intent = $this->payments->stripe->createPaymentIntent($payment);
            $payment->update(['gateway_reference' => $intent['id']]);

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'client_secret' => $intent['client_secret'],
                'publishable_key' => config('payments.stripe.public'),
                'amount' => (float) $payment->amount,
                'currency' => strtolower($payment->currency),
                'simulated' => false,
            ]);
        } catch (PaymentGatewayException $e) {
            Log::error('Stripe create-intent error', ['payment' => $payment->id, 'error' => $e->getMessage()]);
            $this->payments->markFailed($payment, $e->getMessage());

            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * Confirm Stripe payment.
     */
    public function stripeConfirm(Request $request): JsonResponse
    {
        $request->validate([
            'payment_id' => ['required', 'integer'],
            'payment_intent_id' => ['nullable', 'string'],
        ]);

        $payment = Payment::with('enrollment.course')->where('payment_method', 'stripe')->findOrFail($request->integer('payment_id'));

        $intentId = $request->input('payment_intent_id') ?: $payment->gateway_reference ?: 'pi_card_' . Str::random(16);

        $this->payments->markPaid(
            $payment,
            (float) $payment->amount,
            $intentId,
            ['stripe_payment_intent' => $intentId, 'confirmed_client_side' => true]
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed successfully.',
        ]);
    }

    /**
     * Create PayPal order.
     */
    public function payPalCreateOrder(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => ['required', 'integer'],
        ]);

        $course = Course::findOrFail($request->integer('course_id'));
        $user = $request->user();

        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['status' => 'pending', 'progress' => 0, 'enrolled_at' => now()]
        );

        $payment = $this->payments->createAttempt($enrollment, 'paypal');

        if ($this->payments->simulating('paypal')) {
            $orderId = 'PAYID-SIM-' . strtoupper(Str::random(12));
            $payment->update(['gateway_reference' => $orderId, 'meta' => ['simulated' => true]]);

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'order_id' => $orderId,
                'simulated' => true,
            ]);
        }

        try {
            $order = $this->payments->paypal->createOrder(
                $payment,
                url('/api/v1/checkout/paypal/return?payment=' . $payment->id),
                url('/api/v1/checkout/cancel?payment=' . $payment->id)
            );

            $payment->update(['gateway_reference' => $order['id']]);

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'order_id' => $order['id'],
                'approve_url' => $order['approve_url'],
                'simulated' => false,
            ]);
        } catch (PaymentGatewayException $e) {
            Log::error('PayPal create-order error', ['payment' => $payment->id, 'error' => $e->getMessage()]);
            $this->payments->markFailed($payment, $e->getMessage());

            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * Create Cash / Offline payment pledge.
     */
    public function cashCreate(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => ['required', 'integer'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $course = Course::findOrFail($request->integer('course_id'));
        $user = $request->user();

        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['status' => 'pending', 'progress' => 0, 'enrolled_at' => now()]
        );

        $payment = $this->payments->createAttempt($enrollment, 'cash', [
            'status' => 'pending_payment',
            'notes' => $request->filled('notes')
                ? trim($request->input('notes'))
                : 'Awaiting cash payment upon delivery or campus pickup.',
        ]);

        return response()->json([
            'success' => true,
            'payment_id' => $payment->id,
            'status' => 'pending_payment',
            'reference' => $payment->reference,
            'message' => 'Cash payment pledge recorded. Present reference ' . $payment->reference . ' at bursar desk.',
        ]);
    }

    /**
     * Check payment status by ID.
     */
    public function status(Payment $payment): JsonResponse
    {
        return response()->json([
            'payment_id' => $payment->id,
            'status' => $payment->status,
            'amount' => (float) $payment->amount,
            'currency' => $payment->currency,
            'payment_method' => $payment->payment_method,
            'transaction_id' => $payment->transaction_id,
            'is_paid' => $payment->status === 'completed',
        ]);
    }
}
