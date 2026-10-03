<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $announcements = Announcement::visibleTo($user)
            ->with(['author', 'course'])
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(12);

        $courses = match (true) {
            $user->isAdmin() => Course::orderBy('title')->get(),
            $user->isInstructor() => $user->taughtCourses()->orderBy('title')->get(),
            default => collect(),
        };

        return view('announcements.index', compact('announcements', 'courses'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->isAdmin() || $user->isInstructor(), 403);

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'audience' => ['required', Rule::in(['all', 'students', 'instructors'])],
            'is_pinned' => ['boolean'],
        ];

        $validated = $request->validate($rules);
        $validated['user_id'] = $user->id;
        $validated['is_pinned'] = $request->boolean('is_pinned');

        // Instructors can only announce to their own courses
        if ($user->isInstructor()) {
            abort_unless($validated['course_id'] && $user->taughtCourses()->where('id', $validated['course_id'])->exists(), 403);
            $validated['audience'] = 'students';
        }

        Announcement::create($validated);

        return back()->with('success', 'Announcement published.');
    }

    public function destroy(Announcement $announcement)
    {
        $user = auth()->user();
        abort_unless($user->isAdmin() || $announcement->user_id === $user->id, 403);

        $announcement->delete();

        return back()->with('success', 'Announcement removed.');
    }
}
