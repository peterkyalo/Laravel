<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'priority' => $this->priority,
            'target_role' => $this->target_role,
            'course_id' => $this->course_id,
            'author' => new UserResource($this->whenLoaded('author')),
            'course' => new CourseResource($this->whenLoaded('course')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
