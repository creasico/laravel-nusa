<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

/**
 * @property-read \Illuminate\Support\Collection<int, Regency> $regencies
 * @property-read \Illuminate\Support\Collection<int, District> $districts
 * @property-read \Illuminate\Support\Collection<int, Village> $villages
 *
 * @mixin \Creasi\Nusa\Models\Model
 */
interface Province
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|Regency
     */
    public function regencies();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|District
     */
    public function districts();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|Village
     */
    public function villages();
}
