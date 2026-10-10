<?php

namespace App\Http\Requests\Api\V1\Assignment;

use Illuminate\Foundation\Http\FormRequest;

class GradeSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isInstructor() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
