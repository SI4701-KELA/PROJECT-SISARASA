<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'min:3', 'max:100'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:15', 'regex:/^[0-9]*$/'],
            'photo' => [
                'sometimes',
                'nullable',
                File::image()
                    ->max(2048),
            ],
            'email' => ['prohibited'],
            'role' => ['prohibited'],
        ];
    }
}
