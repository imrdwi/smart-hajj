<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PrayerRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'arabic' => ['required', 'string', 'max:5000'],
            'transliteration' => ['nullable', 'string', 'max:5000'],
            'translation' => ['required', 'string', 'max:5000'],
        ];
    }
}
