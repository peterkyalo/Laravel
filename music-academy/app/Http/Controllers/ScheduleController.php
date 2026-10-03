<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Course;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $courses = match (true) {
            $user->isAdmin() => Course::orderBy('title')->get(),
            $user->isInstructor() => $user->taughtCourses()->orderBy('title')->get(),
            default => $user->enrolledCourses()->wherePivotIn('status', ['active', 'completed'])->get(),
        };

        return view('schedule.index', compact('courses'));
    }

    public function events(Request $request)
    {
        $user = auth()->user();

        $query = ClassSession::with('course');

        if ($user->isStudent()) {
            $courseIds = $user->enrollments()
                ->whereIn('status', ['active', 'completed'])
                ->pluck('course_id');
            $query->whereIn('course_id', $courseIds);
        } elseif ($user->isInstructor()) {
            $courseIds = $user->taughtCourses()->pluck('id');
            $query->whereIn('course_id', $courseIds);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $sessions = $query->get()->map->toCalendarEvent();

        return response()->json($sessions);
    }

    public function store(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_url' => ['nullable', 'url', 'max:255'],
        ]);

        $course->sessions()->create($validated);

        return back()->with('success', 'Class session scheduled.');
    }

    public function destroy(ClassSession $session)
    {
        $this->authorizeCourse($session->course);
        $session->delete();

        return back()->with('success', 'Session cancelled.');
    }
}
