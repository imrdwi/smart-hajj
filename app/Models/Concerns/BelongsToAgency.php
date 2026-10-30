<?php

namespace App\Models\Concerns;

/**
 * A record owned by one agency, so staff of other agencies cannot reach it.
 */
interface BelongsToAgency
{
    public function owningAgencyId(): ?int;
}
