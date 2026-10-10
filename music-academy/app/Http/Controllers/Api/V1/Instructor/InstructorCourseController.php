<?php

namespace App\Http\Controllers\Api\V1\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Course\StoreCourseRequest;
use App\Http\Resources\V1\CourseResource;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InstructorCourseController extends Controller
{
    /**
     * List courses taught by instructor (or all for admin).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = Course::query()->with(['instrument', 'instructor'])->withCount(['lessons', 'enrollments']);

        if (! $user->isAdmin()) {
            $query->where('instructor_id', $user->id);
        }

        $courses = $query->latest()->paginate(15);

        return CourseResource::collection($courses);
    }

    /**
     * Create a new course.
     */
    public function store(StoreCourseRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->safe()->except(['thumbnail']);

        $data['instructor_id'] = $user->isAdmin() && $request->filled('instructor_id')
            ? $request->instructor_id
            : $user->id;

        $data['slug'] = Str::slug($request->title) . '-' . Str::random(5);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        $course = Course::create($data);

        return response()->json([
            'message' => 'Course created successfully.',
            'course' => new CourseResource($course->load(['instrument', 'instructor'])),
        ], 201);
    }

    /**
     * Get course management details.
     */
    public function show(Request $request, Course $course): JsonResponse
    {
        $this->authorizeCourse($course);

        $course->load(['instrument', 'instructor', 'lessons' => fn($q) => $q->orderBy('order'), 'assignments', 'quizzes', 'classSessions'])
            ->loadCount(['lessons', 'enrollments']);

        return response()->json([
            'course' => new CourseResource($course),
        ]);
    }

    /**
     * Update course.
     */
    public function update(StoreCourseRequest $request, Course $course): JsonResponse
    {
        $this->authorizeCourse($course);

        $data = $request->safe()->except(['thumbnail']);

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        $course->update($data);

        return response()->json([
            'message' => 'Course updated successfully.',
            'course' => new CourseResource($course->load(['instrument', 'instructor'])),
        ]);
    }

    /**
     * Delete course.
     */
    public function destroy(Request $request, Course $course): JsonResponse
    {
        $this->authorizeCourse($course);

        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        $course->delete();

        return response()->json([
            'message' => 'Course deleted successfully.',
        ]);
    }
}
