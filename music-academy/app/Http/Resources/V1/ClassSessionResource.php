<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'course_title' => $this->course?->title,
            'title' => $this->title,
            'description' => $this->description,
            'meeting_url' => $this->meeting_url,
            'meeting_provider' => $this->meeting_provider,
            'start_time' => $this->start_time?->toISOString(),
            'end_time' => $this->end_time?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
