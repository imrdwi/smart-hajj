<?php

namespace App\Http\Requests\Admin;

use App\Gender;
use App\Models\Pilgrim;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class PilgrimRequest extends AgencyScopedRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $pilgrim = $this->route('pilgrim');

        return [
            'group_id' => ['required', 'integer', $this->existsInAgency('groups')],
            'name' => ['required', 'string', 'max:150'],
            'passport_number' => ['required', 'string', 'max:20', Rule::unique('pilgrims')->ignore($pilgrim instanceof Pilgrim ? $pilgrim->id : null)],
            'gender' => ['required', Rule::enum(Gender::class)],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'phone' => ['nullable', 'string', 'max:30'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
