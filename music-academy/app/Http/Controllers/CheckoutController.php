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

        // Dynamic localization requirement:
        // Check user billing country or IP context (defaultRegion: 'KE').
        // If region is KE or East Africa, default selectedMethod to mpesa. Otherwise, default to stripe.
        $detectedRegion = strtoupper(
            request()->query('region')
            ?? request()->header('CF-IPCountry')
            ?? request()->header('X-Country-Code')
            ?? request()->header('Geoip-Country-Code')
            ?? (preg_match('/^(?:\+?254|07|01)/', (string) ($user->phone ?? '')) ? 'KE' : 'KE')
        );

        $eastAfrica = ['KE', 'UG', 'TZ', 'RW', 'BI', 'SS'];
        $defaultMethod = in_array($detectedRegion, $eastAfrica, true) ? 'mpesa' : 'stripe';
        if (! $methods->contains($defaultMethod)) {
            $defaultMethod = $methods->first() ?? 'mpesa';
        }

        return view('checkout.show', [
            'course' => $course->load('instrument', 'instructor'),
            'enrollment' => $enrollment,
            'balance' => $enrollment->balance(),
            'amountPaid' => $enrollment->amountPaid(),
            'methods' => $methods,
            'defaultMethod' => $defaultMethod,
            'defaultRegion' => $detectedRegion,
            'simulating' => $methods->filter(fn ($m) => $this->payments->simulating($m))->values(),
            'pendingCash' => $enrollment->payments()->where('method', 'cash')->whereIn('status', ['pending', 'pending_payment'])->latest()->first(),
            'kesAmount' => MpesaGateway::toKes($enrollment->balance()),
            'currency' => config('payments.currency', 'USD'),
            'stripePublishableKey' => config('payments.stripe.public'),
            'userPhone' => $user->phone ?? '',
            'userEmail' => $user->email ?? '',
            'userName' => $user->name ?? '',
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

        if ($override) {
            // If they provided a base ngrok URL, ensure we append the correct route path
            $base = rtrim($override, '/');
            if (!str_contains($base, 'payments/mpesa/callback')) {
                $base .= '/payments/mpesa/callback';
            }
            return $base . '/' . $token;
        }

        return route('payments.mpesa.callback', ['token' => $token]);
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

    /* ---------------------------------------------------------------------
     | JSON / AJAX API Endpoints for Multi-Gateway Checkout Selector
     * -------------------------------------------------------------------*/

    public function apiMpesaStkPush(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => ['required', 'integer'],
            'phone' => ['required', 'string', 'max:25'],
        ]);

        $course = Course::findOrFail($request->integer('course_id'));
        $rawPhone = (string) $request->input('phone');
        $phone = MpesaGateway::normalizePhone($rawPhone);

        if (! $phone) {
            return response()->json([
                'success' => false,
                'error' => 'Please enter a valid Kenyan Safaricom phone number (e.g. 0712 345 678, 0110 123 456, or +254 7XX XXX XXX).',
            ], 422);
        }

        [$enrollment, $guard] = $this->prepare($course, 'mpesa');
        if ($guard) {
            return response()->json(['success' => false, 'error' => 'Enrollment or payment method unavailable.'], 403);
        }

        $kes = MpesaGateway::toKes($enrollment->balance());
        $payment = $this->payments->createAttempt($enrollment, 'mpesa', [
            'phone' => $phone,
            'meta' => ['amount_kes' => $kes, 'exchange_rate' => (float) config('payments.mpesa.exchange_rate')],
        ]);

        if ($this->payments->simulating('mpesa') || app()->environment('testing')) {
            $simulatedCheckoutId = 'ws_CO_SIM_'.now()->format('YmdHis').'_'.Str::random(6);
            $payment->mergeMeta(['merchant_request_id' => 'MR_SIM_'.Str::random(8), 'simulated' => true]);
            $payment->fill(['gateway_reference' => $simulatedCheckoutId])->save();

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'checkout_request_id' => $simulatedCheckoutId,
                'customer_message' => "STK Push prompt sent to +{$phone}. Please unlock your device and enter your M-Pesa PIN.",
                'phone' => $phone,
                'amount_kes' => $kes,
                'currency' => 'KES',
                'simulated' => true,
            ]);
        }

        try {
            $result = $this->payments->mpesa->stkPush($payment, $phone, $kes, $this->mpesaCallbackUrl());
        } catch (PaymentGatewayException $e) {
            Log::error('M-Pesa STK push error', ['payment' => $payment->id, 'error' => $e->getMessage()]);
            $this->payments->markFailed($payment, $e->getMessage());

            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }

        $payment->mergeMeta(['merchant_request_id' => $result['MerchantRequestID'] ?? null]);
        $payment->fill(['gateway_reference' => $result['CheckoutRequestID'] ?? null])->save();

        return response()->json([
            'success' => true,
            'payment_id' => $payment->id,
            'checkout_request_id' => $result['CheckoutRequestID'] ?? null,
            'customer_message' => $result['CustomerMessage'] ?? "STK Push prompt sent to +{$phone}. Please unlock your device and enter your M-Pesa PIN.",
            'phone' => $phone,
            'amount_kes' => $kes,
            'currency' => 'KES',
            'simulated' => false,
        ]);
    }

    public function apiMpesaQuery(Request $request): JsonResponse
    {
        $request->validate([
            'payment_id' => ['required', 'integer'],
        ]);

        $payment = Payment::with('enrollment.course')->where('method', 'mpesa')->findOrFail($request->integer('payment_id'));
        $this->authorizePayment($payment);

        if ($payment->isPaid()) {
            return response()->json([
                'status' => 'paid',
                'paid' => true,
                'result_code' => 0,
                'result_desc' => 'The service request is processed successfully.',
                'redirect_url' => route('learning.course', $payment->enrollment->course),
            ]);
        }

        if ($payment->status === 'cancelled') {
            return response()->json([
                'status' => 'cancelled',
                'paid' => false,
                'result_code' => 1032,
                'result_desc' => 'Request cancelled by user or expired.',
            ]);
        }

        if ($payment->status === 'failed') {
            return response()->json([
                'status' => 'failed',
                'paid' => false,
                'result_code' => 1,
                'result_desc' => $payment->notes ?? 'Transaction failed.',
            ]);
        }

        // Sandbox simulator handling
        if ($this->payments->simulating('mpesa') || app()->environment('testing')) {
            $createdSecsAgo = $payment->created_at ? $payment->created_at->diffInSeconds(now()) : 0;
            $forceComplete = $request->boolean('auto_approve') || $request->input('sim_action') === 'success';

            // Auto-complete simulated payment after 6 seconds of polling, or on explicit request
            if ($forceComplete || $createdSecsAgo >= 6) {
                $this->payments->markPaid(
                    $payment,
                    (float) $payment->amount,
                    'SIM-MPESA-'.strtoupper(Str::random(10)),
                    ['simulated' => true, 'auto_approved' => true]
                );

                return response()->json([
                    'status' => 'paid',
                    'paid' => true,
                    'result_code' => 0,
                    'result_desc' => 'The service request is processed successfully.',
                    'redirect_url' => route('learning.course', $payment->enrollment->course),
                ]);
            }

            return response()->json([
                'status' => 'pending',
                'paid' => false,
                'result_code' => -1,
                'result_desc' => 'Awaiting user PIN entry on device.',
            ]);
        }

        // Live Daraja query
        if ($payment->gateway_reference && $this->payments->mpesa->isConfigured()) {
            try {
                $result = $this->payments->mpesa->query($payment->gateway_reference);
            } catch (\Throwable $e) {
                return response()->json([
                    'status' => 'pending',
                    'paid' => false,
                    'result_code' => -1,
                    'result_desc' => 'Checking status with Daraja...',
                ]);
            }

            if (array_key_exists('ResultCode', $result)) {
                $code = (int) $result['ResultCode'];
                $desc = $result['ResultDesc'] ?? '';

                if ($code === 0) {
                    $receiptNo = $result['MpesaReceiptNumber'] ?? $payment->gateway_reference;
                    $this->payments->markPaid($payment, (float) $payment->amount, $receiptNo, ['mpesa_query' => $desc]);
                    return response()->json([
                        'status' => 'paid',
                        'paid' => true,
                        'result_code' => 0,
                        'result_desc' => 'The service request is processed successfully.',
                        'redirect_url' => route('learning.course', $payment->enrollment->course),
                    ]);
                }

                // Code 4999 or description indicating still processing means awaiting PIN entry
                if ($code === 4999 || str_contains(strtolower($desc), 'processing')) {
                    return response()->json([
                        'status' => 'pending',
                        'paid' => false,
                        'result_code' => 4999,
                        'result_desc' => $desc ?: 'Awaiting user PIN entry on device.',
                    ]);
                }

                $this->payments->markFailed($payment, 'M-Pesa: '.$desc, $code === 1032 ? 'cancelled' : 'failed');
                return response()->json([
                    'status' => $code === 1032 ? 'cancelled' : 'failed',
                    'paid' => false,
                    'result_code' => $code,
                    'result_desc' => $desc ?: 'Transaction not completed.',
                ]);
            }
        }

        return response()->json([
            'status' => 'pending',
            'paid' => false,
            'result_code' => -1,
            'result_desc' => 'Awaiting user PIN entry on device.',
        ]);
    }

    public function apiStripeCreateIntent(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => ['required', 'integer'],
            'save_card' => ['nullable', 'boolean'],
        ]);

        $course = Course::findOrFail($request->integer('course_id'));
        [$enrollment, $guard] = $this->prepare($course, 'stripe');
        if ($guard) {
            return response()->json(['success' => false, 'error' => 'Enrollment or payment method unavailable.'], 403);
        }

        $payment = $this->payments->createAttempt($enrollment, 'stripe', [
            'meta' => ['save_card' => (bool) $request->input('save_card', false)],
        ]);

        if ($this->payments->simulating('stripe') || ! $this->payments->stripe->isConfigured()) {
            $simIntentId = 'pi_sim_'.Str::random(24);
            $simSecret = $simIntentId.'_secret_'.Str::random(24);
            $payment->update(['gateway_reference' => $simIntentId, 'meta' => array_merge($payment->meta ?? [], ['simulated' => true])]);

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
        } catch (PaymentGatewayException $e) {
            Log::error('Stripe create-intent error', ['payment' => $payment->id, 'error' => $e->getMessage()]);
            $this->payments->markFailed($payment, $e->getMessage());

            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }

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
    }

    public function apiStripeConfirm(Request $request): JsonResponse
    {
        $request->validate([
            'payment_id' => ['required', 'integer'],
            'payment_intent_id' => ['nullable', 'string'],
        ]);

        $payment = Payment::with('enrollment.course')->where('method', 'stripe')->findOrFail($request->integer('payment_id'));
        $this->authorizePayment($payment);

        $intentId = $request->input('payment_intent_id') ?: $payment->gateway_reference ?: 'pi_card_'.Str::random(16);

        $this->payments->markPaid(
            $payment,
            (float) $payment->amount,
            $intentId,
            ['stripe_payment_intent' => $intentId, 'confirmed_client_side' => true]
        );

        return response()->json([
            'success' => true,
            'redirect_url' => route('learning.course', $payment->enrollment->course),
        ]);
    }

    public function apiPayPalCreateOrder(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => ['required', 'integer'],
        ]);

        $course = Course::findOrFail($request->integer('course_id'));
        [$enrollment, $guard] = $this->prepare($course, 'paypal');
        if ($guard) {
            return response()->json(['success' => false, 'error' => 'Enrollment or payment method unavailable.'], 403);
        }

        $payment = $this->payments->createAttempt($enrollment, 'paypal');

        if ($this->payments->simulating('paypal')) {
            $orderId = 'PAYID-SIM-'.strtoupper(Str::random(12));
            $payment->update(['gateway_reference' => $orderId, 'meta' => ['simulated' => true]]);

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'order_id' => $orderId,
                'approve_url' => route('checkout.simulate', $payment),
                'simulated' => true,
            ]);
        }

        try {
            $order = $this->payments->paypal->createOrder(
                $payment,
                route('checkout.paypal.return', ['payment' => $payment->id]),
                route('checkout.cancel', $payment),
            );
        } catch (PaymentGatewayException $e) {
            Log::error('PayPal create-order error', ['payment' => $payment->id, 'error' => $e->getMessage()]);
            $this->payments->markFailed($payment, $e->getMessage());

            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }

        $payment->update(['gateway_reference' => $order['id']]);

        return response()->json([
            'success' => true,
            'payment_id' => $payment->id,
            'order_id' => $order['id'],
            'approve_url' => $order['approve_url'],
            'simulated' => false,
        ]);
    }

    public function apiCashCreate(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => ['required', 'integer'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $course = Course::findOrFail($request->integer('course_id'));
        [$enrollment, $guard] = $this->prepare($course, 'cash');
        if ($guard) {
            return response()->json(['success' => false, 'error' => 'Enrollment or payment method unavailable.'], 403);
        }

        $existing = $enrollment->payments()
            ->where('method', 'cash')
            ->whereIn('status', ['pending', 'pending_payment'])
            ->latest()
            ->first();

        if ($existing && round((float) $existing->amount, 2) === round($enrollment->balance(), 2)) {
            if ($request->filled('notes')) {
                $existing->update(['notes' => trim($request->input('notes'))]);
            }
            $payment = $existing;
        } else {
            $payment = $this->payments->createAttempt($enrollment, 'cash', [
                'status' => 'pending_payment',
                'notes' => $request->filled('notes')
                    ? trim($request->input('notes'))
                    : 'Awaiting cash payment upon delivery or campus pickup.',
            ]);
        }

        return response()->json([
            'success' => true,
            'payment_id' => $payment->id,
            'status' => 'pending_payment',
            'reference' => $payment->reference,
            'redirect_url' => route('checkout.pending', $payment),
            'message' => 'Cash on delivery / pickup registered. Please present reference '.$payment->reference.' to the bursar.',
        ]);
    }
}

