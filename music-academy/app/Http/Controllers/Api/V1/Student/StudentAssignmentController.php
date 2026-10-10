<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Assignment\SubmitAssignmentRequest;
use App\Http\Resources\V1\AssignmentResource;
use App\Http\Resources\V1\SubmissionResource;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class StudentAssignmentController extends Controller
{
    /**
     * List all assignments across enrolled courses.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $enrolledCourseIds = $user->enrollments()->pluck('course_id');

        $assignments = Assignment::whereIn('course_id', $enrolledCourseIds)
            ->with(['course', 'submissions' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->latest('due_date')
            ->paginate(15);

        return AssignmentResource::collection($assignments);
    }

    /**
     * Get details of a single assignment.
     */
    public function show(Request $request, Assignment $assignment): JsonResponse
    {
        $user = $request->user();
        $submission = $assignment->submissions()->where('user_id', $user->id)->first();

        return response()->json([
            'assignment' => new AssignmentResource($assignment->load('course')),
            'my_submission' => $submission ? new SubmissionResource($submission) : null,
        ]);
    }

    /**
     * Submit assignment files/audio/video.
     */
    public function submit(SubmitAssignmentRequest $request, Assignment $assignment): JsonResponse
    {
        $user = $request->user();

        $submission = Submission::firstOrNew([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
        ]);

        if ($request->hasFile('file')) {
            if ($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }
            $submission->file_path = $request->file('file')->store('submissions/docs', 'public');
        }

        if ($request->hasFile('audio_file')) {
            if ($submission->audio_path) {
                Storage::disk('public')->delete($submission->audio_path);
            }
            $submission->audio_path = $request->file('audio_file')->store('submissions/audio', 'public');
        }

        if ($request->filled('video_url')) {
            $submission->video_url = $request->video_url;
        }

        if ($request->filled('content')) {
            $submission->content = $request->content;
        }

        $submission->save();

        return response()->json([
            'message' => 'Assignment submitted successfully.',
            'submission' => new SubmissionResource($submission->load('user')),
        ], 201);
    }
}
