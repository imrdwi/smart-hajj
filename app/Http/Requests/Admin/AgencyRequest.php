<?php

namespace App\Http\Requests\Admin;

use App\Models\Agency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgencyRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $agency = $this->route('agency');

        return [
            'name' => ['required', 'string', 'max:150'],
            'license_number' => ['nullable', 'string', 'max:50', Rule::unique('agencies')->ignore($agency instanceof Agency ? $agency->id : null)],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }
}
