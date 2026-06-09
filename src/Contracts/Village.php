<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Creasi\Nusa\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $district_code
 * @property-read string $regency_code
 * @property-read string $province_code
 * @property-read null|int $postal_code
 * @property-read Province $province
 * @property-read Regency $regency
 * @property-read District $district
 *
 * @mixin Model
 */
interface Village
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
     * @return BelongsTo<District, $this>
     */
    public function district(): BelongsTo;
}
