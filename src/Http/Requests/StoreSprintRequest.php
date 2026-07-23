<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSprintRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'goal' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'planning_note' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'leader_id' => ['nullable', 'integer'],
            'members' => ['nullable', 'array'],
            'members.*.user_id' => ['required_with:members', 'integer'],
            'members.*.role' => ['nullable', 'in:leader,member'],
            'members.*.display_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
