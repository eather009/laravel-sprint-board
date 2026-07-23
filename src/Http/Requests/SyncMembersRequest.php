<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncMembersRequest extends FormRequest
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
            'members' => ['required', 'array'],
            'members.*.user_id' => ['required', 'integer'],
            'members.*.role' => ['nullable', 'in:leader,member'],
            'members.*.display_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
