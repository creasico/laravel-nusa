<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\HasCoordinate as LatitudeLongitudeContract;
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
class Coordinate extends EloquentModel implements LatitudeLongitudeContract
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

    public function associateWith(
        Village $village,
        ?District $district = null,
        ?Regency $regency = null,
        ?Province $province = null,
    ) {
        $this->village()->associate($village);

        if (! $district) {
            $district = $village->district;
        }

        $this->district()->associate($district);

        if (! $regency) {
            $regency = $village->regency;
        }

        $this->regency()->associate($regency);

        if (! $province) {
            $province = $village->province;
        }

        $this->province()->associate($province);

        return $this->fresh();
    }


    public function coordinateable()
    {
        return $this->morphTo('coordinateable');
    }
}
