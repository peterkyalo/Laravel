<?php

namespace App\Http\Requests\Api\V1\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isInstructor() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
            'pass_score' => ['required', 'integer', 'min:1', 'max:100'],
            'is_published' => ['boolean'],
        ];
    }
}
