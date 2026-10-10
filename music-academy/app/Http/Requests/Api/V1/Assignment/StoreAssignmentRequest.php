<?php

namespace App\Http\Requests\Api\V1\Assignment;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isInstructor() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'due_date' => ['nullable', 'date'],
            'max_score' => ['required', 'integer', 'min:1'],
        ];
    }
}
