<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Services\Payments\MpesaGateway;
use App\Services\Payments\PaymentGatewayException;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(protected PaymentService $payments)
    {
    }

    /* ---------------------------------------------------------------------
     | Checkout page
     * -------------------------------------------------------------------*/

    public function show(Course $course)
    {
        $user = auth()->user();

        if ($user->isAdmin() || $course->instructor_id === $user->id) {
            return redirect()->route('learning.course', $course);
        }

        abort_if($course->status !== 'published', 404);

        $enrollment = $this->resolveEnrollment($course);

        if ($enrollment->isFullyPaid()) {
            $enrollment->activate();

            return redirect()->route('learning.course', $course)->with('success', 'Your tuition is fully paid — welcome to the classroom!');
        }

        $methods = collect(Payment::METHODS)
            ->keys()
            ->filter(fn ($m) => $this->payments->isAvailable($m))
            ->values();

        return view('checkout.show', [
            'course' => $course->load('instrument', 'instructor'),
            'enrollment' => $enrollment,
            'balance' => $enrollment->balance(),
            'amountPaid' => $enrollment->amountPaid(),
            'methods' => $methods,
            'simulating' => $methods->filter(fn ($m) => $this->payments->simulating($m))->values(),
            'pendingCash' => $enrollment->payments()->where('method', 'cash')->where('status', 'pending')->latest()->first(),
            'kesAmount' => MpesaGateway::toKes($enrollment->balance()),
            'currency' => config('payments.currency', 'USD'),
        ]);
    }

    /* ---------------------------------------------------------------------
     | Stripe
     * -------------------------------------------------------------------*/

    public function stripe(Course $course): RedirectResponse
    {
        [$enrollment, $guard] = $this->prepare($course, 'stripe');
        if ($guard) {
            return $guard;
        }

        $payment = $this->payments->createAttempt($enrollment, 'stripe');

        if ($this->payments->simulating('stripe')) {
            return redirect()->route('checkout.simulate', $payment);
        }

        try {
            $session = $this->payments->stripe->createCheckoutSession(
                $payment,
                route('checkout.stripe.return', ['payment' => $payment->id]),
                route('checkout.cancel', $payment),
            );
        } catch (PaymentGatewayException $e) {
            return $this->gatewayError($payment, $course, $e);
        }

        $payment->update(['gateway_reference' => $session['id']]);

        return redirect()->away($session['url']);
    }

    public function stripeReturn(Request $request): RedirectResponse
    {
        $payment = $this->ownedPayment((int) $request->query('payment'), 'stripe');
        $course = $payment->enrollment->course;
        $sessionId = (string) $request->query('session_id');

        abort_unless($sessionId !== '' && hash_equals((string) $payment->gateway_reference, $sessionId), 403);

        try {
            $session = $this->payments->stripe->retrieveSession($sessionId);
        } catch (PaymentGatewayException $e) {
            return redirect()->route('checkout.show', $course)->with('error', 'We could not confirm your Stripe payment yet. If you were charged, it will be confirmed automatically shortly.');
        }

        return $this->completeStripeSession($payment, $session)
            ? $this->success($course)
            : redirect()->route('checkout.show', $course)->with('info', 'Your Stripe payment is still processing. Your classroom unlocks automatically once Stripe confirms it.');
    }

    /** Stripe webhook (signed) — source of truth even if the student closes the browser. */
    public function stripeWebhook(Request $request): JsonResponse
    {
        try {
            $event = $this->payments->stripe->verifyWebhook($request->getContent(), $request->header('Stripe-Signature'));
        } catch (PaymentGatewayException $e) {
            Log::warning('Rejected Stripe webhook: '.$e->getMessage());

            return response()->json(['error' => 'invalid signature'], 400);
        }

        $type = $event['type'] ?? '';
        $session = $event['data']['object'] ?? [];

        if (in_array($type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            $paymentId = $session['metadata']['payment_id'] ?? $session['client_reference_id'] ?? null;
            $payment = Payment::where('method', 'stripe')->find($paymentId);

            if ($payment && hash_equals((string) $payment->gateway_reference, (string) ($session['id'] ?? ''))) {
                $this->completeStripeSession($payment, $session);
            }
        }

        if ($type === 'checkout.session.async_payment_failed' || $type === 'checkout.session.expired') {
            $payment = Payment::where('method', 'stripe')->where('gateway_reference', $session['id'] ?? '')->first();
            if ($payment?->isPending()) {
                $this->payments->markFailed($payment, 'Stripe session '.str_replace('checkout.session.', '', $type).'.', $type === 'checkout.session.expired' ? 'cancelled' : 'failed');
            }
        }

        return response()->json(['received' => true]);
    }

    protected function completeStripeSession(Payment $payment, array $session): bool
    {
        if (($session['payment_status'] ?? null) !== 'paid') {
            return false;
        }

        if (strtoupper($session['currency'] ?? '') !== strtoupper($payment->currency)) {
            $this->payments->markFailed($payment, 'Stripe currency mismatch.');

            return false;
        }

        return $this->payments->markPaid(
            $payment,
            ((int) ($session['amount_total'] ?? 0)) / 100,
            $session['payment_intent'] ?? $session['id'],
            ['stripe_session' => $session['id'] ?? null, 'stripe_payment_intent' => $session['payment_intent'] ?? null, 'payer_email' => $session['customer_details']['email'] ?? null],
        );
    }

    /* ---------------------------------------------------------------------
     | PayPal
     * -------------------------------------------------------------------*/

    public function paypal(Course $course): RedirectResponse
    {
        [$enrollment, $guard] = $this->prepare($course, 'paypal');
        if ($guard) {
            return $guard;
        }

        $payment = $this->payments->createAttempt($enrollment, 'paypal');

        if ($this->payments->simulating('paypal')) {
            return redirect()->route('checkout.simulate', $payment);
        }

        try {
            $order = $this->payments->paypal->createOrder(
                $payment,
                route('checkout.paypal.return', ['payment' => $payment->id]),
                route('checkout.cancel', $payment),
            );
        } catch (PaymentGatewayException $e) {
            return $this->gatewayError($payment, $course, $e);
        }

        $payment->update(['gateway_reference' => $order['id']]);

        return redirect()->away($order['approve_url']);
    }

    public function paypalReturn(Request $request): RedirectResponse
    {
        $payment = $this->ownedPayment((int) $request->query('payment'), 'paypal');
        $course = $payment->enrollment->course;
        $orderId = (string) $request->query('token');

        abort_unless($orderId !== '' && hash_equals((string) $payment->gateway_reference, $orderId), 403);

        if ($payment->isPaid()) {
            return $this->success($course);
        }

        try {
            $order = $this->payments->paypal->captureOrder($orderId);
        } catch (PaymentGatewayException $e) {
            $this->payments->markFailed($payment, $e->getMessage());

            return redirect()->route('checkout.show', $course)->with('error', 'PayPal could not complete the payment. Please try again or choose another method.');
        }

        $capture = $order['purchase_units'][0]['payments']['captures'][0] ?? null;

        if (($order['status'] ?? null) !== 'COMPLETED' || ($capture['status'] ?? null) !== 'COMPLETED') {
            return redirect()->route('checkout.show', $course)->with('info', 'PayPal is still processing your payment. Your classroom unlocks once it completes.');
        }

        if (strtoupper($capture['amount']['currency_code'] ?? '') !== strtoupper($payment->currency)) {
            $this->payments->markFailed($payment, 'PayPal currency mismatch.');

            return redirect()->route('checkout.show', $course)->with('error', 'Payment currency mismatch. Please contact the bursar.');
        }

        $paid = $this->payments->markPaid(
            $payment,
            (float) ($capture['amount']['value'] ?? 0),
            $capture['id'] ?? $orderId,
            ['paypal_order' => $orderId, 'paypal_capture' => $capture['id'] ?? null, 'payer_email' => $order['payer']['email_address'] ?? null],
        );

        return $paid
            ? $this->success($course)
            : redirect()->route('checkout.show', $course)->with('error', 'The amount paid did not match the full tuition. Please contact the bursar.');
    }

    /* ---------------------------------------------------------------------
     | M-Pesa (STK Push)
     * -------------------------------------------------------------------*/

    public function mpesa(Request $request, Course $course): RedirectResponse
    {
        $request->validate(['phone' => ['required', 'string', 'max:20']]);

        $phone = MpesaGateway::normalizePhone($request->input('phone'));
        if (! $phone) {
            return back()->withInput()->withErrors(['phone' => 'Enter a valid Safaricom number, e.g. 0712 345 678.']);
        }

        [$enrollment, $guard] = $this->prepare($course, 'mpesa');
        if ($guard) {
            return $guard;
        }

        $kes = MpesaGateway::toKes($enrollment->balance());
        $payment = $this->payments->createAttempt($enrollment, 'mpesa', [
            'phone' => $phone,
            'meta' => ['amount_kes' => $kes, 'exchange_rate' => (float) config('payments.mpesa.exchange_rate')],
        ]);

        if ($this->payments->simulating('mpesa')) {
            return redirect()->route('checkout.pending', $payment);
        }

        try {
            $result = $this->payments->mpesa->stkPush($payment, $phone, $kes, $this->mpesaCallbackUrl());
        } catch (PaymentGatewayException $e) {
            return $this->gatewayError($payment, $course, $e);
        }

        $payment->mergeMeta(['merchant_request_id' => $result['MerchantRequestID'] ?? null]);
        $payment->fill(['gateway_reference' => $result['CheckoutRequestID'] ?? null])->save();

        return redirect()->route('checkout.pending', $payment)
            ->with('success', $result['CustomerMessage'] ?? 'Check your phone and enter your M-Pesa PIN to complete payment.');
    }

    /** Daraja STK callback (public, CSRF-exempt, protected by a secret URL token). */
    public function mpesaCallback(Request $request, string $token): JsonResponse
    {
        abort_unless(hash_equals((string) config('payments.mpesa.callback_token'), $token), 404);

        $callback = $request->input('Body.stkCallback', []);
        $checkoutId = $callback['CheckoutRequestID'] ?? null;
        $payment = $checkoutId ? Payment::where('method', 'mpesa')->where('gateway_reference', $checkoutId)->first() : null;

        if (! $payment) {
            Log::warning('M-Pesa callback for unknown CheckoutRequestID', ['id' => $checkoutId]);

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        $resultCode = (int) ($callback['ResultCode'] ?? -1);

        if ($resultCode === 0) {
            $items = collect($callback['CallbackMetadata']['Item'] ?? [])->pluck('Value', 'Name');
            $kesPaid = (float) ($items['Amount'] ?? 0);
            $expectedKes = (float) ($payment->meta['amount_kes'] ?? MpesaGateway::toKes((float) $payment->amount));
            $rate = (float) ($payment->meta['exchange_rate'] ?? config('payments.mpesa.exchange_rate'));

            $this->payments->markPaid(
                $payment,
                $kesPaid >= $expectedKes ? (float) $payment->amount : $kesPaid / max($rate, 1),
                (string) ($items['MpesaReceiptNumber'] ?? $payment->reference),
                ['mpesa_receipt' => $items['MpesaReceiptNumber'] ?? null, 'amount_kes_paid' => $kesPaid, 'mpesa_phone' => $items['PhoneNumber'] ?? null],
            );
        } else {
            $this->payments->markFailed(
                $payment,
                'M-Pesa: '.($callback['ResultDesc'] ?? 'transaction not completed'),
                $resultCode === 1032 ? 'cancelled' : 'failed',
                ['mpesa_result_code' => $resultCode],
            );
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    protected function mpesaCallbackUrl(): string
    {
        $token = config('payments.mpesa.callback_token');
        $override = config('payments.mpesa.callback_url');

        return $override
            ? rtrim($override, '/').'/'.$token
            : route('payments.mpesa.callback', ['token' => $token]);
    }

    /* ---------------------------------------------------------------------
     | M-Pesa (C2B PayBill)
     * -------------------------------------------------------------------*/

    public function mpesaC2b(Course $course): RedirectResponse
    {
        [$enrollment, $guard] = $this->prepare($course, 'mpesa');
        if ($guard) {
            return $guard;
        }

        $kes = MpesaGateway::toKes($enrollment->balance());

        $existing = $enrollment->payments()->where('method', 'mpesa')->where('status', 'pending')->latest()->first();

        if ($existing && round((float) $existing->amount, 2) === round($enrollment->balance(), 2)) {
            $payment = $existing;
        } else {
            $payment = $this->payments->createAttempt($enrollment, 'mpesa', [
                'meta' => ['amount_kes' => $kes, 'exchange_rate' => (float) config('payments.mpesa.exchange_rate'), 'c2b_initiated' => true],
            ]);
        }

        return redirect()->route('checkout.pending', $payment)
            ->with('success', 'Use the M-Pesa Paybill instructions to complete your payment.');
    }

    public function mpesaC2bValidate(Request $request, string $token): JsonResponse
    {
        abort_unless(hash_equals((string) config('payments.mpesa.callback_token'), $token), 404);
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function mpesaC2bConfirm(Request $request, string $token): JsonResponse
    {
        abort_unless(hash_equals((string) config('payments.mpesa.callback_token'), $token), 404);

        $payload = $request->all();
        $accountRef = trim($payload['BillRefNumber'] ?? '');
        $kesPaid = (float) ($payload['TransAmount'] ?? 0);
        $receipt = $payload['TransID'] ?? null;
        $phone = $payload['MSISDN'] ?? null;

        if (!$accountRef || !$receipt) {
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Ignored']);
        }

        if (preg_match('/^HMA(\d+)$/i', $accountRef, $matches)) {
            $payment = Payment::where('method', 'mpesa')->find($matches[1]);
        } else {
            Log::warning('M-Pesa C2B received for unknown account', $payload);
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        if ($payment && !$payment->isPaid()) {
            $expectedKes = (float) ($payment->meta['amount_kes'] ?? MpesaGateway::toKes((float) $payment->amount));
            $rate = (float) ($payment->meta['exchange_rate'] ?? config('payments.mpesa.exchange_rate'));

            $this->payments->markPaid(
                $payment,
                $kesPaid >= $expectedKes ? (float) $payment->amount : $kesPaid / max($rate, 1),
                $receipt,
                ['mpesa_receipt' => $receipt, 'amount_kes_paid' => $kesPaid, 'mpesa_phone' => $phone, 'c2b' => true],
            );
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /* ---------------------------------------------------------------------
     | Cash
     * -------------------------------------------------------------------*/

    public function cash(Course $course): RedirectResponse
    {
        [$enrollment, $guard] = $this->prepare($course, 'cash');
        if ($guard) {
            return $guard;
        }

        $existing = $enrollment->payments()->where('method', 'cash')->where('status', 'pending')->latest()->first();

        if ($existing && round((float) $existing->amount, 2) === round($enrollment->balance(), 2)) {
            return redirect()->route('checkout.pending', $existing);
        }

        $payment = $this->payments->createAttempt($enrollment, 'cash', [
            'notes' => 'Awaiting cash payment at the bursar office.',
        ]);

        return redirect()->route('checkout.pending', $payment)
            ->with('success', 'Cash payment reference generated. Bring it to the bursar office to unlock your course.');
    }

    /* ---------------------------------------------------------------------
     | Pending / status / cancel
     * -------------------------------------------------------------------*/

    public function pending(Payment $payment)
    {
        $this->authorizePayment($payment);

        if ($payment->isPaid() && $payment->enrollment->isFullyPaid()) {
            return $this->success($payment->enrollment->course);
        }

        return view('checkout.pending', [
            'payment' => $payment->load('enrollment.course'),
            'course' => $payment->enrollment->course,
            'simulating' => $payment->method === 'mpesa' && $this->payments->simulating('mpesa'),
        ]);
    }

    public function status(Payment $payment): JsonResponse
    {
        $this->authorizePayment($payment);

        // M-Pesa fallback: query Daraja directly if the callback hasn't arrived yet.
        if ($payment->isPending() && $payment->method === 'mpesa' && $payment->gateway_reference
            && $this->payments->mpesa->isConfigured() && $payment->created_at->lt(now()->subSeconds(15))
            && Cache::add('mpesa_query_'.$payment->id, true, 10)) {
            $this->reconcileMpesa($payment);
        }

        $payment->refresh();

        return response()->json([
            'status' => $payment->status,
            'paid' => $payment->isPaid(),
            'message' => $payment->notes,
            'redirect' => $payment->isPaid() ? route('learning.course', $payment->enrollment->course) : null,
        ]);
    }

    protected function reconcileMpesa(Payment $payment): void
    {
        try {
            $result = $this->payments->mpesa->query($payment->gateway_reference);
        } catch (\Throwable $e) {
            return;
        }

        if (! array_key_exists('ResultCode', $result)) {
            return; // still processing
        }

        if ((string) $result['ResultCode'] === '0') {
            $this->payments->markPaid($payment, (float) $payment->amount, null, ['mpesa_query' => $result['ResultDesc'] ?? 'success']);
        } else {
            $this->payments->markFailed($payment, 'M-Pesa: '.($result['ResultDesc'] ?? 'transaction not completed'),
                (string) $result['ResultCode'] === '1032' ? 'cancelled' : 'failed');
        }
    }

    public function cancel(Payment $payment): RedirectResponse
    {
        $this->authorizePayment($payment);

        if ($payment->isPending()) {
            $this->payments->markFailed($payment, 'Cancelled by student before completing payment.', 'cancelled');
        }

        return redirect()->route('checkout.show', $payment->enrollment->course)
            ->with('info', 'Payment was cancelled. You can choose another payment method below.');
    }

    /* ---------------------------------------------------------------------
     | Sandbox simulator (only when a gateway has no credentials & simulation is on)
     * -------------------------------------------------------------------*/

    public function simulate(Payment $payment)
    {
        $this->authorizePayment($payment);
        abort_unless($payment->isPending() && $this->payments->simulating($payment->method), 404);

        return view('checkout.simulate', [
            'payment' => $payment->load('enrollment.course', 'enrollment.user'),
            'course' => $payment->enrollment->course,
        ]);
    }

    public function simulateComplete(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorizePayment($payment);
        abort_unless($payment->isPending() && $this->payments->simulating($payment->method), 404);

        $course = $payment->enrollment->course;

        if ($request->input('outcome') !== 'success') {
            $this->payments->markFailed($payment, 'Declined in sandbox simulator.', $request->input('outcome') === 'fail' ? 'failed' : 'cancelled');

            return redirect()->route('checkout.show', $course)->with('error', 'Sandbox payment was not completed. Please try again.');
        }

        $prefix = ['stripe' => 'pi_sim_', 'paypal' => 'PAYID-SIM-', 'mpesa' => 'SIM'][$payment->method] ?? 'SIM-';

        $this->payments->markPaid($payment, (float) $payment->amount, $prefix.strtoupper(Str::random(10)), ['simulated' => true]);

        return $this->success($course);
    }

    /* ---------------------------------------------------------------------
     | Helpers
     * -------------------------------------------------------------------*/

    /** Find or create the student's enrollment (pending until paid). */
    protected function resolveEnrollment(Course $course): Enrollment
    {
        $user = auth()->user();
        $enrollment = $user->enrollmentFor($course);

        if (! $enrollment) {
            $enrollment = $user->enrollments()->create([
                'course_id' => $course->id,
                'status' => $course->isFree() ? 'active' : 'pending',
                'progress' => 0,
                'enrolled_at' => now(),
            ]);
        } elseif ($enrollment->status === 'cancelled') {
            $enrollment->update(['status' => 'pending']);
        }

        return $enrollment->setRelation('course', $course);
    }

    /**
     * Common guards before starting any gateway.
     *
     * @return array{0: Enrollment|null, 1: RedirectResponse|null}
     */
    protected function prepare(Course $course, string $method): array
    {
        $user = auth()->user();

        if ($user->isAdmin() || $course->instructor_id === $user->id) {
            return [null, redirect()->route('learning.course', $course)];
        }

        abort_unless($this->payments->isAvailable($method), 404, 'This payment method is not available.');

        $enrollment = $this->resolveEnrollment($course);

        if ($enrollment->isFullyPaid()) {
            $enrollment->activate();

            return [null, redirect()->route('learning.course', $course)->with('info', 'This course is already fully paid.')];
        }

        return [$enrollment, null];
    }

    protected function ownedPayment(int $id, string $method): Payment
    {
        $payment = Payment::with('enrollment.course')->where('method', $method)->findOrFail($id);
        $this->authorizePayment($payment);

        return $payment;
    }

    protected function authorizePayment(Payment $payment): void
    {
        abort_unless($payment->enrollment && $payment->enrollment->user_id === auth()->id(), 403);
    }

    protected function gatewayError(Payment $payment, Course $course, PaymentGatewayException $e): RedirectResponse
    {
        Log::error('Payment gateway error', ['payment' => $payment->id, 'error' => $e->getMessage()]);
        $this->payments->markFailed($payment, $e->getMessage());

        return redirect()->route('checkout.show', $course)
            ->with('error', 'The payment provider is unavailable right now. Please try again or choose another method.');
    }

    protected function success(Course $course): RedirectResponse
    {
        return redirect()->route('learning.course', $course)
            ->with('success', 'Payment successful! Your tuition is fully paid and the classroom is now unlocked.');
    }
}
