<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Creasi\Nusa\Models\Regency;

/**
 * @property-read null|\Creasi\Nusa\Contracts\Regency $regency
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait BelongsToRegency
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\Creasi\Nusa\Contracts\Regency
     */
    public function regency()
    {
        return $this->belongsTo(Regency::class);
    }
}
