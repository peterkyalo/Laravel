<?php

namespace App\Http\Requests\Api\V1\Assignment;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStudent() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,zip,mid,midi', 'max:20480'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a,mid,midi', 'max:51200'],
            'video_url' => ['nullable', 'url', 'max:500'],
        ];
    }
}
