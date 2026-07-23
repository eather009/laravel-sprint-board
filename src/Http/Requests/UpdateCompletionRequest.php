<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'completion_status' => ['required', 'in:pending,completed'],
            'completion_note' => ['nullable', 'string'],
        ];
    }
}
