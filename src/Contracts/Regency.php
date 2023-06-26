<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

/**
 * @property-read int $province_code
 * @property-read Province $province
 * @property-read \Illuminate\Support\Collection<int, District> $districts
 * @property-read \Illuminate\Support\Collection<int, Village> $villages
 */
interface Regency extends Model
{
    //
}
