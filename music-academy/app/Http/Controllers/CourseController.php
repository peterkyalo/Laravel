<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Instrument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $courses = Course::query()
            ->when(!$user->isAdmin(), fn ($q) => $q->where('instructor_id', $user->id))
            ->when($request->filled('instrument'), fn ($q) => $q->where('instrument_id', $request->instrument))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->search.'%'))
            ->with(['instrument', 'instructor'])
            ->withCount(['lessons', 'enrollments'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $instruments = Instrument::orderBy('name')->get();

        return view('courses.index', compact('courses', 'instruments'));
    }

    public function create()
    {
        $instruments = Instrument::orderBy('name')->get();
        $instructors = auth()->user()->isAdmin() 
            ? User::where('role', 'instructor')->where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('courses.create', compact('instruments', 'instructors'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'instrument_id' => ['required', 'exists:instruments,id'],
            'level' => ['required', Rule::in(Course::LEVELS)],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'fee' => ['required', 'numeric', 'min:0'],
            'duration_weeks' => ['nullable', 'integer', 'min:1', 'max:52'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'cover_image' => ['nullable', 'image', 'max:3072'],
        ];

        if ($user->isAdmin()) {
            $rules['instructor_id'] = ['required', 'exists:users,id'];
        }

        $validated = $request->validate($rules);

        if (!$user->isAdmin()) {
            $validated['instructor_id'] = $user->id;
        }

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('courses/covers', 'public');
        }

        $course = Course::create($validated);

        return redirect()->route('courses.show.manage', $course)->with('success', 'Course created successfully. Now add some lessons!');
    }

    public function manage(Course $course)
    {
        $this->authorizeCourse($course);

        $course->load([
            'instrument',
            'instructor',
            'lessons' => fn ($q) => $q->orderBy('position'),
            'assignments',
            'quizzes',
            'sessions' => fn ($q) => $q->orderBy('starts_at'),
            'enrollments.user',
        ]);

        return view('courses.manage', compact('course'));
    }

    public function edit(Course $course)
    {
        $this->authorizeCourse($course);

        $instruments = Instrument::orderBy('name')->get();
        $instructors = auth()->user()->isAdmin() 
            ? User::where('role', 'instructor')->where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('courses.edit', compact('course', 'instruments', 'instructors'));
    }

    public function update(Request $request, Course $course)
    {
        $this->authorizeCourse($course);
        $user = auth()->user();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'instrument_id' => ['required', 'exists:instruments,id'],
            'level' => ['required', Rule::in(Course::LEVELS)],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'fee' => ['required', 'numeric', 'min:0'],
            'duration_weeks' => ['nullable', 'integer', 'min:1', 'max:52'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'cover_image' => ['nullable', 'image', 'max:3072'],
        ];

        if ($user->isAdmin()) {
            $rules['instructor_id'] = ['required', 'exists:users,id'];
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('cover_image')) {
            if ($course->cover_image && !str_starts_with($course->cover_image, 'images/')) {
                Storage::disk('public')->delete($course->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('courses/covers', 'public');
        }

        $course->update($validated);

        return redirect()->route('courses.show.manage', $course)->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $this->authorizeCourse($course);

        if ($course->cover_image && !str_starts_with($course->cover_image, 'images/')) {
            Storage::disk('public')->delete($course->cover_image);
        }

        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Course deleted successfully.');
    }
}
