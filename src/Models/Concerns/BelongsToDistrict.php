<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Creasi\Nusa\Models\District;

/**
 * @property-read null|\Creasi\Nusa\Contracts\District $district
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait BelongsToDistrict
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\Creasi\Nusa\Contracts\District
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
