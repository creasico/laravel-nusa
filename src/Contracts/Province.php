<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read \Illuminate\Support\Collection<int, Regency> $regencies
 * @property-read \Illuminate\Support\Collection<int, District> $districts
 * @property-read \Illuminate\Support\Collection<int, Village> $villages
 *
 * @mixin \Creasi\Nusa\Models\Model
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
