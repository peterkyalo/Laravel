<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isInstructorOrAdmin = $request->user()?->isAdmin() || $request->user()?->isInstructor();

        return [
            'id' => $this->id,
            'question_id' => $this->question_id,
            'option_text' => $this->option_text,
            'is_correct' => $this->when($isInstructorOrAdmin || $request->routeIs('*results*'), (bool) $this->is_correct),
        ];
    }
}
