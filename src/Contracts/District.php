<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

/**
 * @property-read int $regency_code
 * @property-read int $province_code
 * @property-read Province $province
 * @property-read Regency $regency
 * @property-read \Illuminate\Support\Collection<int, Village> $villages
 */
interface District extends Model
{
    //
}
