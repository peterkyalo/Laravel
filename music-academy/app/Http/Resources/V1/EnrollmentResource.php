<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'course_id' => $this->course_id,
            'status' => $this->status,
            'progress' => (int) $this->progress,
            'is_fully_paid' => (bool) ($this->resource->relationLoaded('course') || $this->course ? $this->isFullyPaid() : true),
            'total_paid' => (float) $this->amountPaid(),
            'balance' => (float) ($this->resource->relationLoaded('course') || $this->course ? $this->balance() : 0),
            'enrolled_at' => $this->enrolled_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'course' => new CourseResource($this->whenLoaded('course')),
            'user' => new UserResource($this->whenLoaded('user')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}
