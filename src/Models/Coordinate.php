<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Coordinate as CoordinateContract;
use Creasi\Nusa\Contracts\District;
use Creasi\Nusa\Contracts\Province;
use Creasi\Nusa\Contracts\Regency;
use Creasi\Nusa\Contracts\Village;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @property-read null|Province $province
 * @property-read null|Regency $regency
 * @property-read null|District $district
 * @property-read null|Village $village
 *
 * @mixin \Illuminate\Contracts\Database\Eloquent\Builder
 */
class Coordinate extends EloquentModel implements CoordinateContract
{
    use Concerns\WithDistrict;
    use Concerns\WithProvince;
    use Concerns\WithRegency;
    use Concerns\WithVillage;

    public function getCasts()
    {
        return \array_merge(parent::getCasts(), [
            'latitude' => 'float',
            'longitude' => 'float',
        ]);
    }

    public function getFillable()
    {
        return \array_merge(parent::getFillable(), ['latitude', 'longitude']);
    }
    public function getTable()
    {
        return config('creasi.nusa.table_names.coordinates', parent::getTable());
    }
}
