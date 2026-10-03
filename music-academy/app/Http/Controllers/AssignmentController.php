<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isStudent()) {
            $enrolledCourseIds = $user->enrollments()
                ->whereIn('status', ['active', 'completed'])
                ->pluck('course_id');

            $assignments = Assignment::whereIn('course_id', $enrolledCourseIds)
                ->with(['course', 'submissions' => fn ($q) => $q->where('user_id', $user->id)])
                ->latest()
                ->paginate(10);

            return view('assignments.student_index', compact('assignments'));
        }

        // Instructor / Admin
        $courseIds = $user->isAdmin()
            ? Course::pluck('id')
            : $user->taughtCourses()->pluck('id');

        $assignments = Assignment::whereIn('course_id', $courseIds)
            ->with('course')
            ->withCount(['submissions', 'submissions as ungraded_count' => fn ($q) => $q->whereNull('graded_at')])
            ->latest()
            ->paginate(12);

        return view('assignments.index', compact('assignments'));
    }

    public function create(Course $course)
    {
        $this->authorizeCourse($course);
        $lessons = $course->lessons()->orderBy('position')->get();

        return view('assignments.create', compact('course', 'lessons'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'lesson_id' => ['nullable', 'exists:lessons,id'],
            'instructions' => ['required', 'string'],
            'due_at' => ['nullable', 'date'],
            'max_score' => ['required', 'integer', 'min:10', 'max:1000'],
        ]);

        $course->assignments()->create($validated);

        return redirect()->route('courses.show.manage', $course)->with('success', 'Assignment added.');
    }

    public function show(Assignment $assignment)
    {
        $user = auth()->user();

        if ($user->isStudent()) {
            abort_unless($user->canAccessCourse($assignment->course), 403);
            $submission = $assignment->submissions()->where('user_id', $user->id)->first();
            return view('assignments.show_student', compact('assignment', 'submission'));
        }

        $this->authorizeCourse($assignment->course);
        $assignment->load(['course', 'submissions.student', 'submissions.grader']);

        return view('assignments.show_instructor', compact('assignment'));
    }

    public function submit(Request $request, Assignment $assignment)
    {
        $user = auth()->user();
        abort_unless($user->canAccessCourse($assignment->course), 403);

        $validated = $request->validate([
            'recording' => ['required', 'file', 'mimes:mp3,wav,m4a,ogg,aac,mp4,webm,mov,pdf', 'max:51200'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $file = $request->file('recording');
        $path = $file->store('submissions', 'public');

        Submission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'user_id' => $user->id],
            [
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'notes' => $validated['notes'] ?? null,
                'score' => null,
                'feedback' => null,
                'graded_at' => null,
                'graded_by' => null,
            ]
        );

        return redirect()->route('assignments.show', $assignment)->with('success', 'Practice recording submitted successfully! Your instructor will review it.');
    }

    public function grade(Request $request, Submission $submission)
    {
        $this->authorizeCourse($submission->assignment->course);

        $validated = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:'.$submission->assignment->max_score],
            'feedback' => ['required', 'string'],
        ]);

        $submission->update([
            'score' => $validated['score'],
            'feedback' => $validated['feedback'],
            'graded_at' => now(),
            'graded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Submission evaluated and feedback saved.');
    }

    public function destroy(Assignment $assignment)
    {
        $this->authorizeCourse($assignment->course);
        $course = $assignment->course;

        foreach ($assignment->submissions as $sub) {
            Storage::disk('public')->delete($sub->file_path);
        }

        $assignment->delete();

        return redirect()->route('courses.show.manage', $course)->with('success', 'Assignment deleted.');
    }
}
