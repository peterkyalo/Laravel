<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function create(Course $course)
    {
        $this->authorizeCourse($course);
        return view('quizzes.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'pass_mark' => ['required', 'integer', 'min:1', 'max:100'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:180'],
            'is_required' => ['boolean'],
            'is_published' => ['boolean'],
        ]);

        $validated['is_required'] = $request->boolean('is_required', true);
        $validated['is_published'] = $request->boolean('is_published');

        $quiz = $course->quizzes()->create($validated);

        return redirect()->route('quizzes.manage', $quiz)->with('success', 'Quiz created! Now add your questions and choices.');
    }

    public function manage(Quiz $quiz)
    {
        $this->authorizeCourse($quiz->course);
        $quiz->load(['questions.options', 'course']);

        return view('quizzes.manage', compact('quiz'));
    }

    public function addQuestion(Request $request, Quiz $quiz)
    {
        $this->authorizeCourse($quiz->course);

        $validated = $request->validate([
            'text' => ['required', 'string'],
            'points' => ['required', 'integer', 'min:1', 'max:50'],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['required', 'string', 'max:255'],
            'correct_option' => ['required', 'integer'],
        ]);

        $nextPos = ($quiz->questions()->max('position') ?? 0) + 1;

        $question = $quiz->questions()->create([
            'text' => $validated['text'],
            'points' => $validated['points'],
            'position' => $nextPos,
        ]);

        foreach ($validated['options'] as $index => $optText) {
            $question->options()->create([
                'text' => $optText,
                'is_correct' => ((int)$validated['correct_option'] === (int)$index),
            ]);
        }

        return back()->with('success', 'Question added.');
    }

    public function deleteQuestion(Quiz $quiz, $questionId)
    {
        $this->authorizeCourse($quiz->course);
        $question = $quiz->questions()->findOrFail($questionId);
        $question->options()->delete();
        $question->delete();

        return back()->with('success', 'Question deleted.');
    }

    public function togglePublish(Quiz $quiz)
    {
        $this->authorizeCourse($quiz->course);
        $quiz->update(['is_published' => !$quiz->is_published]);

        return back()->with('success', $quiz->is_published ? 'Quiz published!' : 'Quiz moved to draft.');
    }

    public function show(Quiz $quiz)
    {
        $user = auth()->user();
        abort_unless($user->canAccessCourse($quiz->course), 403);

        $attempts = $quiz->attempts()->where('user_id', $user->id)->latest()->get();
        $bestAttempt = $quiz->bestAttemptBy($user);

        return view('quizzes.show', compact('quiz', 'attempts', 'bestAttempt'));
    }

    public function take(Quiz $quiz)
    {
        $user = auth()->user();
        abort_unless($user->canAccessCourse($quiz->course), 403);
        abort_unless($quiz->is_published, 404, 'Quiz is not yet published.');

        $quiz->load(['questions.options']);

        return view('quizzes.take', compact('quiz'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        $user = auth()->user();
        abort_unless($user->canAccessCourse($quiz->course), 403);

        $answers = $request->input('answers', []);
        $attempt = $quiz->grade($user, $answers);

        // Update student's course enrollment progress if this quiz was required
        $enrollment = $user->enrollmentFor($quiz->course);
        if ($enrollment) {
            $enrollment->recalculateProgress();
        }

        return redirect()->route('quizzes.result', [$quiz, $attempt]);
    }

    public function result(Quiz $quiz, QuizAttempt $attempt)
    {
        $user = auth()->user();
        abort_unless($attempt->user_id === $user->id || $user->isAdmin() || $quiz->course->instructor_id === $user->id, 403);

        $quiz->load('questions.options');

        return view('quizzes.result', compact('quiz', 'attempt'));
    }

    public function destroy(Quiz $quiz)
    {
        $this->authorizeCourse($quiz->course);
        $course = $quiz->course;
        $quiz->delete();

        return redirect()->route('courses.show.manage', $course)->with('success', 'Quiz deleted.');
    }
}
