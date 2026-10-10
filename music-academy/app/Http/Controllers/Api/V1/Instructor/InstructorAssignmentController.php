<?php

namespace App\Http\Controllers\Api\V1\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Assignment\GradeSubmissionRequest;
use App\Http\Requests\Api\V1\Assignment\StoreAssignmentRequest;
use App\Http\Resources\V1\AssignmentResource;
use App\Http\Resources\V1\SubmissionResource;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstructorAssignmentController extends Controller
{
    /**
     * Create assignment for a course.
     */
    public function store(StoreAssignmentRequest $request, Course $course): JsonResponse
    {
        $this->authorizeCourse($course);

        $assignment = $course->assignments()->create($request->validated());

        return response()->json([
            'message' => 'Assignment created successfully.',
            'assignment' => new AssignmentResource($assignment),
        ], 201);
    }

    /**
     * Delete assignment.
     */
    public function destroy(Request $request, Assignment $assignment): JsonResponse
    {
        $this->authorizeCourse($assignment->course);

        $assignment->delete();

        return response()->json([
            'message' => 'Assignment deleted successfully.',
        ]);
    }

    /**
     * Grade student submission.
     */
    public function grade(GradeSubmissionRequest $request, Submission $submission): JsonResponse
    {
        $this->authorizeCourse($submission->assignment->course);

        $submission->update([
            'grade' => $request->grade,
            'feedback' => $request->feedback,
            'graded_at' => now(),
        ]);

        return response()->json([
            'message' => 'Submission graded successfully.',
            'submission' => new SubmissionResource($submission->load('user')),
        ]);
    }
}
