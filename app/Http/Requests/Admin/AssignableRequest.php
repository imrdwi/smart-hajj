<?php

namespace App\Http\Requests\Admin;

/**
 * Shared assignment fields for schedules and meals.
 */
abstract class AssignableRequest extends AgencyScopedRequest
{
    /**
     * @return array<string, array<mixed>>
     */
    protected function assignmentRules(): array
    {
        return [
            'flight_group_ids' => ['array'],
            'flight_group_ids.*' => ['integer', $this->existsInAgency('flight_groups')],
            'group_ids' => ['array'],
            'group_ids.*' => ['integer', $this->existsInAgency('groups')],
            'pilgrim_ids' => ['array'],
            'pilgrim_ids.*' => ['integer', $this->existsInAgency('pilgrims')],
        ];
    }

    /**
     * @return array{0: list<int>, 1: list<int>, 2: list<int>}
     */
    public function assignments(): array
    {
        $ids = fn (string $key): array => array_values(array_map('intval', (array) $this->input($key, [])));

        return [$ids('flight_group_ids'), $ids('group_ids'), $ids('pilgrim_ids')];
    }
}
