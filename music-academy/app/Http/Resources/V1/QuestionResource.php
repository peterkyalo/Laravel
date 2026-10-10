<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'question_text' => $this->question_text,
            'audio_url' => $this->audio_path ? asset('storage/' . $this->audio_path) : null,
            'points' => (int) $this->points,
            'options' => QuestionOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}
