<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Address as AddressContract;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @property-read null|EloquentModel $owner
 *
 * @mixin \Illuminate\Contracts\Database\Eloquent\Builder
 */
class Address extends EloquentModel implements AddressContract
{
    use Concerns\BelongsToDistrict;
    use Concerns\BelongsToProvince;
    use Concerns\BelongsToRegency;
    use Concerns\BelongsToVillage;

    public function getCasts()
    {
        return \array_merge($this->casts, [
            'village_code' => 'int',
            'district_code' => 'int',
            'regency_code' => 'int',
            'province_code' => 'int',
            'postal_code' => 'int',
        ]);
    }

    public function getFillable()
    {
        return \array_merge($this->fillable, [
            'line',
            'village_code',
            'district_code',
            'regency_code',
            'province_code',
            'postal_code',
        ]);
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
    public function owner()
    {
        return $this->morphTo();
    }
}
