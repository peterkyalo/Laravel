<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PaymentResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminPaymentController extends Controller
{
    /**
     * List all payments.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Payment::query()->with(['user', 'enrollment.course']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        $payments = $query->latest()->paginate(20);

        return PaymentResource::collection($payments);
    }

    /**
     * Manually record a payment for a student.
     */
    public function record(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $validated['user_id'], 'course_id' => $validated['course_id']],
            ['status' => 'pending', 'progress' => 0, 'enrolled_at' => now()]
        );

        $payment = Payment::create([
            'user_id' => $validated['user_id'],
            'enrollment_id' => $enrollment->id,
            'amount' => $validated['amount'],
            'currency' => 'USD',
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? ('REC-' . strtoupper(uniqid())),
            'status' => 'completed',
            'paid_at' => now(),
            'notes' => $validated['notes'],
        ]);

        if ($enrollment->isFullyPaid()) {
            $enrollment->update(['status' => 'active']);
        }

        return response()->json([
            'message' => 'Payment recorded successfully.',
            'payment' => new PaymentResource($payment->load(['user', 'enrollment.course'])),
        ], 201);
    }

    /**
     * Approve pending offline payment.
     */
    public function approve(Payment $payment): JsonResponse
    {
        $payment->update([
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        $enrollment = $payment->enrollment;
        if ($enrollment && $enrollment->isFullyPaid()) {
            $enrollment->update(['status' => 'active']);
        }

        return response()->json([
            'message' => 'Payment approved and enrollment updated.',
            'payment' => new PaymentResource($payment->load(['user', 'enrollment.course'])),
        ]);
    }

    /**
     * Reject pending payment.
     */
    public function reject(Payment $payment): JsonResponse
    {
        $payment->update([
            'status' => 'failed',
        ]);

        return response()->json([
            'message' => 'Payment rejected.',
            'payment' => new PaymentResource($payment->load(['user', 'enrollment.course'])),
        ]);
    }
}
