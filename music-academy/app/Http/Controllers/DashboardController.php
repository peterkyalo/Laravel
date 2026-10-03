<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();

        return match ($user->role) {
            'admin' => $this->admin($user),
            'instructor' => $this->instructor($user),
            default => $this->student($user),
        };
    }

    private function announcementsFor(User $user)
    {
        return Announcement::visibleTo($user)->with('author', 'course')
            ->orderByDesc('is_pinned')->latest()->take(4)->get();
    }

    private function admin(User $user)
    {
        // Revenue for the last 6 months (for the chart).
        $months = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));
        $revenue = $months->mapWithKeys(fn ($m) => [
            $m->format('M') => (float) Payment::where('status', 'paid')
                ->whereBetween('paid_at', [$m, $m->copy()->endOfMonth()])->sum('amount'),
        ]);

        $byInstrument = Enrollment::join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->join('instruments', 'instruments.id', '=', 'courses.instrument_id')
            ->select('instruments.name', DB::raw('count(*) as total'))
            ->groupBy('instruments.name')->pluck('total', 'name');

        return view('dashboard.admin', [
            'stats' => [
                'students' => User::where('role', 'student')->count(),
                'instructors' => User::where('role', 'instructor')->count(),
                'courses' => Course::count(),
                'revenue' => (float) Payment::where('status', 'paid')->sum('amount'),
                'pending_payments' => Payment::where('status', 'pending')->count(),
                'active_enrollments' => Enrollment::where('status', 'active')->count(),
            ],
            'revenue' => $revenue,
            'byInstrument' => $byInstrument,
            'pendingPayments' => Payment::where('status', 'pending')->with('enrollment.user', 'enrollment.course')->latest()->take(5)->get(),
            'recentEnrollments' => Enrollment::with('user', 'course')->latest()->take(6)->get(),
            'announcements' => $this->announcementsFor($user),
        ]);
    }

    private function instructor(User $user)
    {
        $courseIds = $user->taughtCourses()->pluck('id');

        return view('dashboard.instructor', [
            'stats' => [
                'courses' => $courseIds->count(),
                'students' => Enrollment::whereIn('course_id', $courseIds)->whereIn('status', ['active', 'completed'])->distinct('user_id')->count('user_id'),
                'to_grade' => Submission::whereNull('graded_at')->whereHas('assignment', fn ($q) => $q->whereIn('course_id', $courseIds))->count(),
                'sessions_week' => ClassSession::whereIn('course_id', $courseIds)->whereBetween('starts_at', [now(), now()->addWeek()])->count(),
            ],
            'courses' => $user->taughtCourses()->with('instrument')->withCount(['lessons', 'enrollments'])->get(),
            'toGrade' => Submission::whereNull('graded_at')
                ->whereHas('assignment', fn ($q) => $q->whereIn('course_id', $courseIds))
                ->with('assignment.course', 'student')->latest()->take(6)->get(),
            'upcoming' => ClassSession::whereIn('course_id', $courseIds)->where('starts_at', '>=', now())
                ->with('course')->orderBy('starts_at')->take(5)->get(),
            'announcements' => $this->announcementsFor($user),
        ]);
    }

    private function student(User $user)
    {
        $enrollments = $user->enrollments()->with(['course.instrument', 'course.instructor', 'payments', 'certificate'])->latest()->get();
        $activeCourseIds = $enrollments->whereIn('status', ['active', 'completed'])->pluck('course_id');

        return view('dashboard.student', [
            'enrollments' => $enrollments,
            'stats' => [
                'active' => $enrollments->where('status', 'active')->count(),
                'completed' => $enrollments->where('status', 'completed')->count(),
                'avg_progress' => (int) round($enrollments->whereIn('status', ['active', 'completed'])->avg('progress') ?? 0),
                'balance' => $enrollments->whereIn('status', ['pending', 'active', 'completed'])->sum(fn ($e) => $e->balance()),
            ],
            'dueAssignments' => Assignment::whereIn('course_id', $activeCourseIds)
                ->whereDoesntHave('submissions', fn ($q) => $q->where('user_id', $user->id))
                ->with('course')->orderByRaw('due_at IS NULL, due_at')->take(5)->get(),
            'upcoming' => ClassSession::whereIn('course_id', $activeCourseIds)->where('starts_at', '>=', now())
                ->with('course')->orderBy('starts_at')->take(5)->get(),
            'announcements' => $this->announcementsFor($user),
        ]);
    }
}
