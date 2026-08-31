<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreIoTDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'deviceId' => ['required', 'string', 'max:100', 'exists:devices,id'],
            'recordedAt' => ['required', 'date'],
            'temperature' => ['required', 'numeric'],
            'humidity' => ['required', 'numeric', 'between:0,100'],
            'smokePpm' => ['required', 'numeric', 'min:0'],
            'flameDetected' => ['required', 'boolean'],
            'flameConfirmed' => ['nullable', 'boolean'],
            'state' => ['nullable', 'string', 'max:20'],
            'fan' => ['nullable', 'boolean'],
            'pump' => ['nullable', 'boolean'],
            'buzzer' => ['nullable', 'boolean'],
        ];
    }
}
