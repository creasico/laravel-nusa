<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Model;
use Creasi\Nusa\Models\Regency;
use Creasi\Nusa\Models\Village;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @template TModel of Model
 *
 * @extends HasSubdivision<\Creasi\Nusa\Models\Regency>
 *
 * @property-read Collection<int, Regency> $regencies
 * @property-read Collection<int, District> $districts
 * @property-read Collection<int, Village> $villages
 *
 * @mixin TModel
 */
interface Province extends HasSubdivision
{
    /**
     * @return HasMany<Regency, TModel>
     */
    public function regencies(): HasMany;

    /**
     * @return HasMany<District, TModel>
     */
    public function districts(): HasMany;

    /**
     * @return HasMany<Village, TModel>
     */
    public function villages(): HasMany;
}
