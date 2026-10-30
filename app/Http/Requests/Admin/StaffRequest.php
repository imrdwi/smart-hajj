<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StaffRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $staff = $this->route('user');
        $isSystemAdmin = (bool) $this->user()?->isSystemAdmin();

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($staff instanceof User ? $staff->id : null)],
            'role' => ['required', Rule::enum(UserRole::class)->only($isSystemAdmin ? UserRole::cases() : [UserRole::AgencyAdmin, UserRole::GroupLeader])],
            'agency_id' => $isSystemAdmin
                ? ['nullable', 'required_unless:role,'.UserRole::SystemAdmin->value, 'prohibited_if:role,'.UserRole::SystemAdmin->value, 'integer', 'exists:agencies,id']
                : ['prohibited'],
            'password' => [$staff instanceof User ? 'nullable' : 'required', 'string', Password::defaults()],
        ];
    }
}
