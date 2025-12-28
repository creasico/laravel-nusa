<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

/**
 * @property-read null|float $latitude
 * @property-read null|float $longitude
 * @property-read null|array $coordinates
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface HasCoordinate
{
    // .
}
