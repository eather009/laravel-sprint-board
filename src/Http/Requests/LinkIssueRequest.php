<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LinkIssueRequest extends FormRequest
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
            'external_project_id' => ['required', 'string', 'max:100'],
            'external_issue_id' => ['required', 'string', 'max:100'],
            'tracker' => ['nullable', 'string', 'max:50'],
            'priority_id' => ['nullable', 'integer'],
        ];
    }
}
