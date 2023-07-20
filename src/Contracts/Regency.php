<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

/**
 * @property-read int $province_code
 * @property-read Province $province
 * @property-read \Illuminate\Support\Collection<int, District> $districts
 * @property-read \Illuminate\Support\Collection<int, Village> $villages
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface Regency
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Province
     */
    public function province();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|District
     */
    public function districts();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|Village
     */
    public function villages();
}
