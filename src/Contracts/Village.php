<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

/**
 * @property-read int $district_code
 * @property-read int $regency_code
 * @property-read int $province_code
 * @property-read ?int $postal_code
 * @property-read Province $province
 * @property-read Regency $regency
 * @property-read District $district
 */
interface Village extends Model
{
    //
}
