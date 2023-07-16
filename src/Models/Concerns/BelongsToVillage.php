<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Creasi\Nusa\Models\Village;

/**
 * @property-read null|\Creasi\Nusa\Contracts\Village $village
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait BelongsToVillage
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\Creasi\Nusa\Contracts\Village
     */
    public function village()
    {
        return $this->belongsTo(Village::class);
    }
}
