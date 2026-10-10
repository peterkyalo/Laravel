<?php

namespace App\Http\Requests\Api\V1\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStudent() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*' => ['required', 'integer', 'exists:question_options,id'],
        ];
    }
}
