<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $latestAttempt = null;

        if ($user && $this->relationLoaded('attempts')) {
            $latestAttempt = $this->attempts->where('user_id', $user->id)->sortByDesc('created_at')->first();
        }

        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            'time_limit_minutes' => $this->time_limit_minutes,
            'pass_score' => $this->pass_score,
            'is_published' => (bool) $this->is_published,
            'questions_count' => $this->whenCounted('questions'),
            'questions' => QuestionResource::collection($this->whenLoaded('questions')),
            'latest_attempt' => $latestAttempt ? new QuizAttemptResource($latestAttempt) : null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
