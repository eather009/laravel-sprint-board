<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSprintRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'goal' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'planning_note' => ['nullable', 'string'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date'],
            'leader_id' => ['sometimes', 'integer'],
            'status' => ['sometimes', 'in:planning,running,completed'],
            'retrospective' => ['nullable', 'array'],
            'dashboard_settings' => ['nullable', 'array'],
        ];
    }
}
