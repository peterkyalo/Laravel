<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ClassSessionResource;
use App\Models\ClassSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentScheduleController extends Controller
{
    /**
     * List live class sessions for student enrolled courses.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $enrolledCourseIds = $user->enrollments()->pluck('course_id');

        $sessions = ClassSession::whereIn('course_id', $enrolledCourseIds)
            ->with('course')
            ->where('end_time', '>=', now()->subDays(1))
            ->orderBy('start_time')
            ->get();

        return ClassSessionResource::collection($sessions);
    }
}
