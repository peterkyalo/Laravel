<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function enroll(Course $course)
    {
        $user = auth()->user();

        if ($user->isAdmin() || $course->instructor_id === $user->id) {
            return redirect()->route('learning.course', $course);
        }

        $existing = $user->enrollments()->where('course_id', $course->id)->first();
        if ($existing) {
            if ($existing->status === 'cancelled') {
                $existing->update(['status' => $course->isFree() ? 'active' : 'pending']);
            }
            if ($existing->isFullyPaid() || $course->isFree()) {
                $existing->activate();
                return redirect()->route('learning.course', $course);
            }
            return redirect()->route('checkout.show', $course);
        }

        // If free, activate immediately. If paid, pending payment.
        $status = $course->isFree() ? 'active' : 'pending';

        $enrollment = $user->enrollments()->create([
            'course_id' => $course->id,
            'status' => $status,
            'progress' => 0,
            'enrolled_at' => now(),
        ]);

        if ($status === 'active') {
            return redirect()->route('learning.course', $course)->with('success', 'Enrolled successfully! Enjoy your lessons.');
        }

        return redirect()->route('checkout.show', $course)->with('info', 'Course selected. Please choose your payment method below.');
    }

    public function index()
    {
        $user = auth()->user();
        $enrollments = $user->enrollments()
            ->with([
                'course' => fn ($q) => $q->withCount('lessons'),
                'course.instrument',
                'course.instructor',
                'payments',
                'certificate',
            ])
            ->latest()
            ->paginate(9);

        return view('student.courses', compact('enrollments'));
    }
}
