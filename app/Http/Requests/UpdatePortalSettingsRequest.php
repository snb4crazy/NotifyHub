<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePortalSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'notification_preferences' => ['nullable', 'array'],
            'notification_preferences.push_enabled' => ['nullable', 'boolean'],
            'notification_preferences.minimum_severity' => ['nullable', 'in:info,warning,error,critical'],
        ];
    }
}

