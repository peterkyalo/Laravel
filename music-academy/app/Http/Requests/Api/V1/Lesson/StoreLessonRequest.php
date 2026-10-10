<?php

namespace App\Http\Requests\Api\V1\Lesson;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isInstructor() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'string', 'max:500'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3,wav,ogg,midi,mid', 'max:25600'],
            'sheet_music_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'position' => ['nullable', 'integer', 'min:0'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_preview' => ['nullable', 'boolean'],
            'is_free_preview' => ['nullable', 'boolean'],
        ];
    }
}
