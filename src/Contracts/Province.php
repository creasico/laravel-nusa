<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Creasi\Nusa\Models\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property-read Collection<int, Regency> $regencies
 * @property-read Collection<int, District> $districts
 * @property-read Collection<int, Village> $villages
 *
 * @mixin Model
 */
interface Province extends HasSubdivision
{
    /**
     * @return HasMany<Regency, $this>
     */
    public function regencies(): HasMany;

    /**
     * @return HasMany<District, $this>
     */
    public function districts(): HasMany;

    /**
     * @return HasMany<Village, $this>
     */
    public function villages(): HasMany;
}
