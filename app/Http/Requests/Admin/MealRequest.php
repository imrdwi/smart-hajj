<?php

namespace App\Http\Requests\Admin;

use App\MealType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class MealRequest extends AssignableRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(MealType::class)],
            'menu' => ['nullable', 'string', 'max:1000'],
            'served_at' => ['required', 'date'],
            ...$this->assignmentRules(),
        ];
    }
}
