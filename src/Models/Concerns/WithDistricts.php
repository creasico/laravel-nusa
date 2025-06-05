<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Creasi\Nusa\Models\District;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, District|\Creasi\Nusa\Contracts\District> $districts
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait WithDistricts
{
    /**
     * @return HasMany<District, TDeclaringModel>
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}
