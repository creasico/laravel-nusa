<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Creasi\Nusa\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property-read string $regency_code
 * @property-read string $province_code
 * @property-read Province $province
 * @property-read Regency $regency
 * @property-read Collection<int, Village> $villages
 *
 * @mixin Model
 */
interface District extends HasSubdivision
{
    /**
     * @return BelongsTo<Province, $this>
     */
    public function province(): BelongsTo;

    /**
     * @return BelongsTo<Regency, $this>
     */
    public function regency(): BelongsTo;

    /**
     * @return HasMany<Village, $this>
     */
    public function villages(): HasMany;
}
