<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read null|float $latitude
 * @property-read null|float $longitude
 * @property-read null|array $coordinates
 *
 * @mixin Model
 */
interface HasCoordinate
{
    // .
}
