<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $userSubmission = null;

        if ($user && $this->relationLoaded('submissions')) {
            $userSubmission = $this->submissions->where('user_id', $user->id)->first();
        }

        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'course_title' => $this->course?->title,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date?->toISOString(),
            'max_score' => $this->max_score,
            'created_at' => $this->created_at?->toISOString(),
            'my_submission' => $userSubmission ? new SubmissionResource($userSubmission) : null,
            'submissions' => SubmissionResource::collection($this->whenLoaded('submissions')),
        ];
    }
}
