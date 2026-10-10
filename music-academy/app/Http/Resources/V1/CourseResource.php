<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'tagline' => $this->tagline,
            'description' => $this->description,
            'level' => $this->level,
            'price' => (float) $this->price,
            'is_free' => (bool) $this->is_free,
            'duration_weeks' => $this->duration_weeks,
            'thumbnail' => $this->thumbnail ? asset('storage/' . $this->thumbnail) : null,
            'status' => $this->status,
            'is_published' => $this->status === 'published',
            'instructor' => new UserResource($this->whenLoaded('instructor')),
            'instrument' => new InstrumentResource($this->whenLoaded('instrument')),
            'lessons' => LessonResource::collection($this->whenLoaded('lessons')),
            'lessons_count' => $this->whenCounted('lessons'),
            'enrollments_count' => $this->whenCounted('enrollments'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
