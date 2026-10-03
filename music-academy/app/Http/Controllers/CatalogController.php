<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Instrument;
use App\Models\User;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function home()
    {
        return view('public.home', [
            'instruments' => Instrument::withCount(['courses' => fn ($q) => $q->published()])->orderBy('name')->get(),
            'featured' => Course::published()->with(['instrument', 'instructor'])->withCount('lessons')->latest()->take(6)->get(),
            'instructors' => User::where('role', 'instructor')->where('is_active', true)->withCount('taughtCourses')->take(4)->get(),
            'stats' => [
                'students' => User::where('role', 'student')->count(),
                'courses' => Course::published()->count(),
                'instructors' => User::where('role', 'instructor')->count(),
                'certificates' => Enrollment::where('status', 'completed')->count(),
            ],
        ]);
    }

    public function about()
    {
        return view('public.about', [
            'instructors' => User::where('role', 'instructor')->where('is_active', true)->withCount('taughtCourses')->get(),
            'stats' => [
                'students' => User::where('role', 'student')->count(),
                'courses' => Course::published()->count(),
                'instructors' => User::where('role', 'instructor')->count(),
                'certificates' => Enrollment::where('status', 'completed')->count(),
            ],
        ]);
    }

    public function index(Request $request)
    {
        $courses = Course::published()
            ->with(['instrument', 'instructor'])
            ->withCount('lessons')
            ->when($request->filled('instrument'), fn ($q) => $q->whereHas('instrument', fn ($i) => $i->where('slug', $request->instrument)))
            ->when($request->filled('level'), fn ($q) => $q->where('level', $request->level))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('title', 'like', '%'.$request->q.'%')
                ->orWhere('short_description', 'like', '%'.$request->q.'%')))
            ->when($request->sort === 'price_low', fn ($q) => $q->orderBy('fee'))
            ->when($request->sort === 'price_high', fn ($q) => $q->orderByDesc('fee'))
            ->when(! in_array($request->sort, ['price_low', 'price_high']), fn ($q) => $q->latest())
            ->paginate(9)
            ->withQueryString();

        return view('public.courses.index', [
            'courses' => $courses,
            'instruments' => Instrument::orderBy('name')->get(),
            'levels' => Course::LEVELS,
        ]);
    }

    public function show(Course $course)
    {
        $user = auth()->user();
        abort_if($course->status !== 'published' && ! ($user && ($user->isAdmin() || $course->instructor_id === $user->id)), 404);

        $course->load(['instrument', 'instructor', 'lessons', 'quizzes' => fn ($q) => $q->where('is_published', true)])
            ->loadCount(['students', 'assignments']);

        return view('public.courses.show', [
            'course' => $course,
            'enrollment' => $user?->enrollmentFor($course),
            'upcomingSessions' => $course->sessions()->where('starts_at', '>=', now())->take(3)->get(),
            'related' => Course::published()->where('instrument_id', $course->instrument_id)
                ->where('id', '!=', $course->id)->with('instrument')->take(3)->get(),
        ]);
    }
}
