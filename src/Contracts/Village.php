<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

/**
 * @property-read string $district_code
 * @property-read string $regency_code
 * @property-read string $province_code
 * @property-read null|int $postal_code
 * @property-read Province $province
 * @property-read Regency $regency
 * @property-read District $district
 *
 * @mixin \Creasi\Nusa\Models\Model
 */
interface Village
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Province
     */
    public function province();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Regency
     */
    public function regency();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|District
     */
    public function district();
}
