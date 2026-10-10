<?php

namespace App\Http\Requests\Api\V1\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class AddQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isInstructor() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'question_text' => ['required', 'string'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3,wav,ogg,midi,mid', 'max:25600'],
            'points' => ['required', 'integer', 'min:1'],
            'options' => ['required', 'array', 'min:2'],
            'options.*.text' => ['required', 'string'],
            'options.*.is_correct' => ['required', 'boolean'],
        ];
    }
}
