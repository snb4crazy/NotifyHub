<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:140'],
            'message' => ['required', 'string', 'max:5000'],
            'severity' => ['required', Rule::in(['info', 'warning', 'error', 'critical'])],
            'application' => ['nullable', 'string', 'max:120'],
            'context' => ['nullable', 'array'],
            'sensitive_context' => ['nullable', 'array'],
            'occurred_at' => ['nullable', 'date'],
            'fingerprint' => ['nullable', 'string', 'max:255'],
        ];
    }
}

