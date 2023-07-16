<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Creasi\Nusa\Models\Province;

/**
 * @property-read null|\Creasi\Nusa\Contracts\Province $province
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait BelongsToProvince
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\Creasi\Nusa\Contracts\Province
     */
    public function province()
    {
        return $this->belongsTo(Province::class);
    }
}
