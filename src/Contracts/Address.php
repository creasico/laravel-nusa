<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

/**
 * @property-read int $id
 * @property string $line
 * @property ?int $village_code
 * @property ?int $district_code
 * @property ?int $regency_code
 * @property ?int $province_code
 * @property ?int $postal_code
 * @property-read ?Village $village
 * @property-read ?District $district
 * @property-read ?Regency $regency
 * @property-read ?Province $province
 */
interface Address
{
    //
}
