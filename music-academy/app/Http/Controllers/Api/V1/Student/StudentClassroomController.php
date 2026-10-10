<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CourseResource;
use App\Http\Resources\V1\EnrollmentResource;
use App\Http\Resources\V1\LessonResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentClassroomController extends Controller
{
    /**
     * List all enrolled courses for student.
     */
    public function courses(Request $request): AnonymousResourceCollection
    {
        $enrollments = Enrollment::where('user_id', $request->user()->id)
            ->with(['course.instructor', 'course.instrument'])
            ->latest('enrolled_at')
            ->paginate(12);

        return EnrollmentResource::collection($enrollments);
    }

    /**
     * Self-enroll in a course (for free courses or initiating enrollment).
     */
    public function enroll(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();

        $existing = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Already enrolled in this course.',
                'enrollment' => new EnrollmentResource($existing),
            ]);
        }

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => $course->is_free || $course->price == 0 ? 'active' : 'pending',
            'progress' => 0,
            'enrolled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Enrollment successful.',
            'enrollment' => new EnrollmentResource($enrollment->load('course')),
        ], 201);
    }

    /**
     * Get classroom overview for an enrolled course.
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();
        $course = Course::where('slug', $slug)
            ->with(['instructor', 'instrument', 'lessons' => function ($q) {
                $q->orderBy('position')->with('progress');
            }])
            ->firstOrFail();

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        $canAccess = $user->canAccessCourse($course);

        return response()->json([
            'course' => new CourseResource($course),
            'enrollment' => $enrollment ? new EnrollmentResource($enrollment) : null,
            'can_access' => $canAccess,
            'lessons' => LessonResource::collection($course->lessons),
        ]);
    }

    /**
     * Get individual lesson data inside classroom.
     */
    public function lesson(Request $request, string $slug, Lesson $lesson): JsonResponse
    {
        $user = $request->user();
        $course = Course::where('slug', $slug)->firstOrFail();

        if ($lesson->course_id !== $course->id) {
            return response()->json(['message' => 'Lesson does not belong to this course.'], 404);
        }

        if (! $lesson->is_free_preview && ! $user->canAccessCourse($course)) {
            return response()->json(['message' => 'Enrollment and full payment required.'], 403);
        }

        $progress = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        return response()->json([
            'course' => new CourseResource($course),
            'lesson' => new LessonResource($lesson->load('progress')),
            'is_completed' => (bool) ($progress?->is_completed ?? false),
        ]);
    }

    /**
     * Toggle lesson completion status and recalculate course progress.
     */
    public function toggleLessonComplete(Request $request, string $slug, Lesson $lesson): JsonResponse
    {
        $user = $request->user();
        $course = Course::where('slug', $slug)->firstOrFail();

        $progress = LessonProgress::firstOrNew([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
        ]);

        $progress->is_completed = ! $progress->is_completed;
        $progress->completed_at = $progress->is_completed ? now() : null;
        $progress->save();

        // Update overall enrollment progress percentage
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($enrollment) {
            $totalLessons = $course->lessons()->count();
            if ($totalLessons > 0) {
                $completedCount = LessonProgress::where('user_id', $user->id)
                    ->whereIn('lesson_id', $course->lessons()->pluck('id'))
                    ->where('is_completed', true)
                    ->count();

                $percent = round(($completedCount / $totalLessons) * 100);
                $enrollment->update([
                    'progress' => $percent,
                    'status' => $percent >= 100 ? 'completed' : 'active',
                    'completed_at' => $percent >= 100 ? now() : null,
                ]);
            }
        }

        return response()->json([
            'message' => $progress->is_completed ? 'Lesson marked as completed.' : 'Lesson marked as incomplete.',
            'is_completed' => (bool) $progress->is_completed,
            'course_progress' => $enrollment ? $enrollment->progress : null,
        ]);
    }
}
