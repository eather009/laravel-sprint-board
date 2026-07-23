<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRetrospectiveRequest extends FormRequest
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
            'retrospective' => ['required', 'array'],
        ];
    }
}
