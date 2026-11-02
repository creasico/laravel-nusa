<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Creasi\Nusa\Models\Model;
use Creasi\Nusa\Models\Village;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @template TModel of Model
 *
 * @extends HasSubdivision<\Creasi\Nusa\Models\Village>
 *
 * @property-read string $regency_code
 * @property-read string $province_code
 * @property-read Province<\Creasi\Nusa\Models\Province> $province
 * @property-read Regency<\Creasi\Nusa\Models\Regency> $regency
 * @property-read Collection<int, Village> $villages
 *
 * @mixin TModel
 */
interface District extends HasSubdivision
{
    /**
     * @return BelongsTo<\Creasi\Nusa\Models\Province, TModel>
     */
    public function province(): BelongsTo;

    /**
     * @return BelongsTo<\Creasi\Nusa\Models\Regency, TModel>
     */
    public function regency(): BelongsTo;

    /**
     * @return HasMany<Village, TModel>
     */
    public function villages(): HasMany;
}
