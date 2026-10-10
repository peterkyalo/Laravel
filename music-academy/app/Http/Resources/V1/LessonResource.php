<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isCompleted = false;

        if ($user && $this->relationLoaded('progress')) {
            $isCompleted = $this->progress->where('user_id', $user->id)->first()?->is_completed ?? false;
        }

        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->when($request->routeIs('*classroom*') || $request->routeIs('*instructor*') || $request->routeIs('*manage*') || $request->routeIs('*lessons.show*'), $this->content),
            'video_url' => $this->video_url,
            'audio_url' => $this->audio_url ? asset('storage/' . $this->audio_url) : null,
            'sheet_music_url' => $this->sheet_music_url ? asset('storage/' . $this->sheet_music_url) : null,
            'tab_url' => $this->tab_url ? asset('storage/' . $this->tab_url) : null,
            'materials' => $this->materials,
            'duration_minutes' => $this->duration_minutes,
            'order' => $this->order,
            'is_free_preview' => (bool) $this->is_free_preview,
            'is_completed' => (bool) $isCompleted,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
