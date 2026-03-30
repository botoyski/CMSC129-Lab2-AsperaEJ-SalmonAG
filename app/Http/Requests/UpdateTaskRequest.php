<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['Not Started', 'In Progress', 'Completed'])],
            'priority' => ['required', Rule::in(['High', 'Medium', 'Low'])],
            'due_date' => ['required', 'date'],
            'due_time' => ['nullable', 'date_format:H:i'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'A task title is required.',
            'status.required' => 'Please choose the current status.',
            'priority.required' => 'Please pick a priority level.',
            'due_date.required' => 'Please set a due date.',
            'category_id.exists' => 'The selected category is invalid.',
        ];
    }
}
