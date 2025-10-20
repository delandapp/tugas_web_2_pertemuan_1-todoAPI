<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\TodoStatus;

class StoreTodoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(TodoStatus::values())],
            'priority' => ['nullable', 'integer', 'between:1,5'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'completed_at' => ['nullable', 'date'],
        ];
    }
}
