<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;

class LearningController extends Controller
{
    public function course(Course $course)
    {
        $this->authorizeLearning($course);

        $firstLesson = $course->lessons()->orderBy('position')->first();

        if (!$firstLesson) {
            return view('learning.empty', compact('course'));
        }

        return redirect()->route('learning.lesson', [$course, $firstLesson]);
    }

    public function lesson(Course $course, Lesson $lesson)
    {
        $this->authorizeLearning($course);
        abort_unless($lesson->course_id === $course->id, 404);

        $user = auth()->user();

        $course->load([
            'lessons' => fn ($q) => $q->orderBy('position'),
            'quizzes' => fn ($q) => $q->where('is_published', true),
            'assignments',
        ]);

        $completedLessonIds = LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $course->lessons->pluck('id'))
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->toArray();

        // Previous and Next lessons
        $allLessons = $course->lessons;
        $currentIndex = $allLessons->search(fn ($l) => $l->id === $lesson->id);
        $prevLesson = $currentIndex > 0 ? $allLessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex < ($allLessons->count() - 1) ? $allLessons[$currentIndex + 1] : null;

        $enrollment = $user->enrollmentFor($course);

        return view('learning.lesson', compact(
            'course',
            'lesson',
            'completedLessonIds',
            'prevLesson',
            'nextLesson',
            'enrollment'
        ));
    }

    public function toggleComplete(Request $request, Course $course, Lesson $lesson)
    {
        $this->authorizeLearning($course);
        $user = auth()->user();

        $progress = LessonProgress::firstOrNew([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
        ]);

        if ($progress->completed_at) {
            $progress->completed_at = null;
            $progress->save();
            $status = 'uncompleted';
        } else {
            $progress->completed_at = now();
            $progress->save();
            $status = 'completed';
        }

        $enrollment = $user->enrollmentFor($course);
        if ($enrollment) {
            $enrollment->recalculateProgress();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $status,
                'progress' => $enrollment?->progress ?? 0,
            ]);
        }

        return back()->with('success', $status === 'completed' ? 'Lesson marked as completed! Bravo!' : 'Lesson marked as incomplete.');
    }
}
