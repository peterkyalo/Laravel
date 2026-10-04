<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isStudent()) {
            $enrollments = $user->enrollments()->with(['course.instructor', 'course.instrument', 'payments'])->get();
            $payments = Payment::whereHas('enrollment', fn ($q) => $q->where('user_id', $user->id))
                ->with('enrollment.course')
                ->latest()
                ->paginate(10);

            $selectedId = (int) ($request->query('enrollment_id') ?? session('checkout_enrollment_id') ?? 0);
            $selectedEnrollment = $enrollments->firstWhere('id', $selectedId)
                ?? $enrollments->filter(fn ($e) => $e->balance() > 0 || $e->status === 'pending')->first()
                ?? $enrollments->first();

            return view('payments.student_index', compact('enrollments', 'payments', 'selectedEnrollment'));
        }

        abort_unless($user->isAdmin(), 403);

        $payments = Payment::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), fn ($q) => $q->whereHas('enrollment.user', fn ($u) => $u->where('name', 'like', '%'.$request->search.'%')))
            ->with(['enrollment.user', 'enrollment.course', 'recorder'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total_received' => (float) Payment::where('status', 'paid')->sum('amount'),
            'pending_count' => Payment::where('status', 'pending')->count(),
            'pending_amount' => (float) Payment::where('status', 'pending')->sum('amount'),
        ];

        return view('payments.admin_index', compact('payments', 'stats'));
    }

    public function submit(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $enrollment = $user->enrollments()->findOrFail($validated['enrollment_id']);

        $enrollment->payments()->create([
            'amount' => $validated['amount'],
            'method' => $validated['method'],
            'reference' => $validated['reference'],
            'notes' => $validated['notes'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Payment proof submitted. Academy administration will verify and activate your course.');
    }

    public function record(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $enrollment = Enrollment::findOrFail($validated['enrollment_id']);

        $payment = $enrollment->payments()->create([
            'amount' => $validated['amount'],
            'method' => $validated['method'],
            'reference' => $validated['reference'],
            'notes' => $validated['notes'],
            'status' => 'paid',
            'paid_at' => now(),
            'recorded_by' => auth()->id(),
        ]);

        $enrollment->activate();

        return back()->with('success', 'Payment recorded and enrollment activated.');
    }

    public function approve(Payment $payment)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
            'recorded_by' => auth()->id(),
        ]);

        $payment->enrollment->activate();

        return back()->with('success', 'Payment approved and course activated.');
    }

    public function reject(Payment $payment)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $payment->update([
            'status' => 'rejected',
            'recorded_by' => auth()->id(),
        ]);

        return back()->with('info', 'Payment marked as rejected.');
    }
}
