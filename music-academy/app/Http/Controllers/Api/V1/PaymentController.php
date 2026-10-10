<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PaymentResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    /**
     * List payments for authenticated student (or all if admin).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = Payment::query()->with(['enrollment.course', 'user']);

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $payments = $query->latest()->paginate(15);

        return PaymentResource::collection($payments);
    }

    /**
     * Submit offline payment proof (bank transfer, receipt, etc.).
     */
    public function submitProof(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'proof_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $course = Course::findOrFail($request->course_id);

        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['status' => 'pending', 'progress' => 0, 'enrolled_at' => now()]
        );

        $proofPath = $request->file('proof_document')->store('payments/proofs', 'public');

        $payment = Payment::create([
            'user_id' => $user->id,
            'enrollment_id' => $enrollment->id,
            'amount' => $request->amount,
            'currency' => 'USD',
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id ?? ('OFFLINE-' . strtoupper(uniqid())),
            'status' => 'pending',
            'proof_document' => $proofPath,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Payment proof submitted successfully. Awaiting admin approval.',
            'payment' => new PaymentResource($payment->load(['enrollment.course', 'user'])),
        ], 201);
    }
}
