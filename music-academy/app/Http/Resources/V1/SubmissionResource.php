<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assignment_id' => $this->assignment_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'content' => $this->content,
            'file_url' => $this->file_path ? asset('storage/' . $this->file_path) : null,
            'audio_url' => $this->audio_path ? asset('storage/' . $this->audio_path) : null,
            'video_url' => $this->video_url,
            'grade' => $this->grade !== null ? (float) $this->grade : null,
            'feedback' => $this->feedback,
            'graded_at' => $this->graded_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
