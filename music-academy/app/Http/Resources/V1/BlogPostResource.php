<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'content' => $this->content,
            'featured_image' => $this->featured_image ? asset('storage/' . $this->featured_image) : null,
            'tags' => $this->tags,
            'is_published' => (bool) $this->is_published,
            'published_at' => $this->published_at?->toISOString(),
            'author' => new UserResource($this->whenLoaded('author')),
            'comments' => BlogCommentResource::collection($this->whenLoaded('comments')),
            'comments_count' => $this->whenCounted('comments'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
