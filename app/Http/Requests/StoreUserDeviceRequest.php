<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:120'],
            'platform' => ['nullable', 'string', 'max:20'],
            'fcm_token' => ['required', 'string', 'max:2048'],
            'notifications_enabled' => ['nullable', 'boolean'],
        ];
    }
}
