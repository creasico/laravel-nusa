<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Model;
use Creasi\Nusa\Models\Village;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @template TModel of Model
 *
 * @extends HasSubdivision<\Creasi\Nusa\Models\District>
 *
 * @property-read string $province_code
 * @property-read Province<\Creasi\Nusa\Models\Province> $province
 * @property-read Collection<int, District> $districts
 * @property-read Collection<int, Village> $villages
 *
 * @mixin TModel
 */
interface Regency extends HasSubdivision
{
    /**
     * @return BelongsTo<\Creasi\Nusa\Models\Province, TModel>
     */
    public function province(): BelongsTo;

    /**
     * @return HasMany<District, TModel>
     */
    public function districts(): HasMany;

    /**
     * @return HasMany<Village, TModel>
     */
    public function villages(): HasMany;
}
