<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    /**
     * The API key middleware already gates access, so this request only validates payload shape.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validate the normalized event payload received from Laravel apps.
     *
     * The rules intentionally stay strict on size and type so that the storage layer and the
     * future mobile API can rely on a predictable event schema.
     *
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_type' => ['filled', 'string', 'min:1', 'max:80'],
            'title' => ['required', 'string', 'max:140'],
            'message' => ['required', 'string', 'max:5000'],
            'severity' => ['required', Rule::in(['info', 'warning', 'error', 'critical'])],
            'application' => ['nullable', 'string', 'max:120'],
            'environment' => ['filled', 'string', 'min:1', 'max:50'],
            'context' => ['nullable', 'array'],
            'sensitive_context' => ['nullable', 'array'],
            'occurred_at' => ['nullable', 'date'],
            'fingerprint' => ['nullable', 'string', 'max:255'],
        ];
    }
}
