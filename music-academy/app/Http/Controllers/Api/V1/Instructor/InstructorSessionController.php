<?php

namespace App\Http\Controllers\Api\V1\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Schedule\StoreSessionRequest;
use App\Http\Resources\V1\ClassSessionResource;
use App\Models\ClassSession;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstructorSessionController extends Controller
{
    /**
     * Create live class session for a course.
     */
    public function store(StoreSessionRequest $request, Course $course): JsonResponse
    {
        $this->authorizeCourse($course);

        $session = $course->classSessions()->create($request->validated());

        return response()->json([
            'message' => 'Class session scheduled successfully.',
            'session' => new ClassSessionResource($session),
        ], 201);
    }

    /**
     * Delete class session.
     */
    public function destroy(Request $request, ClassSession $session): JsonResponse
    {
        $this->authorizeCourse($session->course);

        $session->delete();

        return response()->json([
            'message' => 'Session deleted successfully.',
        ]);
    }
}
