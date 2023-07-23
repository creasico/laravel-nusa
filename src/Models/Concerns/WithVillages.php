<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Creasi\Nusa\Models\Village;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Creasi\Nusa\Contracts\Village> $villages
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait WithVillages
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\Creasi\Nusa\Contracts\Village
     */
    public function villages()
    {
        return $this->hasMany(Village::class);
    }
}
