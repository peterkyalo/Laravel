<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->enrollment?->user_id ?? $this->user_id,
            'enrollment_id' => $this->enrollment_id,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'method' => $this->method ?? $this->payment_method,
            'payment_method' => $this->method ?? $this->payment_method,
            'reference' => $this->reference ?? $this->transaction_id,
            'transaction_id' => $this->reference ?? $this->transaction_id,
            'status' => $this->status,
            'is_paid' => in_array($this->status, ['paid', 'completed'], true),
            'notes' => $this->notes,
            'paid_at' => $this->paid_at?->toISOString(),
            'user' => new UserResource($this->whenLoaded('user')),
            'enrollment' => new EnrollmentResource($this->whenLoaded('enrollment')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
