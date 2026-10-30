<?php

namespace App\Http\Requests\Admin;

use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;

class GroupRequest extends AgencyScopedRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'flight_group_id' => ['required', 'integer', $this->existsInAgency('flight_groups')],
            'name' => ['required', 'string', 'max:100'],
            'leader_id' => ['nullable', 'integer', $this->existsInAgency('users')->where('role', UserRole::GroupLeader->value)],
        ];
    }
}
