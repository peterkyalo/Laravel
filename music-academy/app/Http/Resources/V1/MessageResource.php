<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sender_id' => $this->sender_id,
            'recipient_id' => $this->recipient_id,
            'parent_id' => $this->parent_id,
            'subject' => $this->subject,
            'body' => $this->body,
            'read_at' => $this->read_at?->toISOString(),
            'is_read' => (bool) $this->read_at,
            'sender' => new UserResource($this->whenLoaded('sender')),
            'recipient' => new UserResource($this->whenLoaded('recipient')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
