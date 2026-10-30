<?php

namespace App\Http\Requests\Admin;

use App\Models\FlightGroup;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class FlightGroupRequest extends AgencyScopedRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $flightGroup = $this->route('flight_group');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('flight_groups')
                ->where('agency_id', $this->agencyId())
                ->ignore($flightGroup instanceof FlightGroup ? $flightGroup->id : null)],
            'airline' => ['nullable', 'string', 'max:100'],
            'flight_number' => ['nullable', 'string', 'max:20'],
            'departs_at' => ['required', 'date'],
            'returns_at' => ['nullable', 'date', 'after:departs_at'],
        ];
    }
}
