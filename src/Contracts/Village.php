<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Creasi\Nusa\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @template TModel of Model
 *
 * @property-read string $district_code
 * @property-read string $regency_code
 * @property-read string $province_code
 * @property-read null|int $postal_code
 * @property-read Province<\Creasi\Nusa\Models\Province> $province
 * @property-read Regency<\Creasi\Nusa\Models\Regency> $regency
 * @property-read District<\Creasi\Nusa\Models\District> $district
 *
 * @mixin Model
 */
interface Village
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
     * @return BelongsTo<\Creasi\Nusa\Models\District, TModel>
     */
    public function district(): BelongsTo;
}
