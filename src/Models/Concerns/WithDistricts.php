<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Creasi\Nusa\Models\District;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
 * @property-read Collection<int, District|\Creasi\Nusa\Contracts\District> $districts
 *
 * @mixin Model
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
