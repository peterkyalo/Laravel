<?php

namespace App\Http\Requests\Api\V1\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isInstructor() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'instrument_id' => ['required', 'exists:instruments,id'],
            'title' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'level' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'price' => ['required', 'numeric', 'min:0'],
            'is_free' => ['boolean'],
            'duration_weeks' => ['required', 'integer', 'min:1'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'archived'])],
        ];
    }
}
