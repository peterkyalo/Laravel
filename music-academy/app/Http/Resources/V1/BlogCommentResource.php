<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogCommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'blog_post_id' => $this->blog_post_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'comment' => $this->comment,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
