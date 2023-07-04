<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Address as AddressContract;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @property-read ?EloquentModel $owner
 * @property-read ?Province $province
 * @property-read ?Regency $regency
 * @property-read ?District $district
 * @property-read ?Village $village
 *
 * @mixin \Illuminate\Contracts\Database\Eloquent\Builder
 */
class Address extends EloquentModel implements AddressContract
{
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Village
     */
    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|District
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Regency
     */
    public function regency()
    {
        return $this->belongsTo(Regency::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Province
     */
    public function province()
    {
        return $this->belongsTo(Province::class);
    }
}
