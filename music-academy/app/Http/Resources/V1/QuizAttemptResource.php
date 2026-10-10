<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'score' => (float) $this->score,
            'passed' => (bool) $this->passed,
            'answers' => $this->answers,
            'completed_at' => $this->created_at?->toISOString(),
        ];
    }
}
