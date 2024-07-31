<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Address as AddressContract;
use Creasi\Nusa\Contracts\District;
use Creasi\Nusa\Contracts\Province;
use Creasi\Nusa\Contracts\Regency;
use Creasi\Nusa\Contracts\Village;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @mixin \Illuminate\Contracts\Database\Eloquent\Builder
 */
class Address extends EloquentModel implements AddressContract
{
    use Concerns\WithDistrict;
    use Concerns\WithProvince;
    use Concerns\WithRegency;
    use Concerns\WithVillage;

    public function getCasts()
    {
        return \array_merge(parent::getCasts(), [
            'postal_code' => 'int',
        ]);
    }

    public function getFillable()
    {
        return \array_merge(parent::getFillable(), ['line', 'postal_code']);
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function addressable()
    {
        return $this->morphTo('addressable');
    }
}
