<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Creasi\Nusa\Models\District;

/**
 * @property-read District|\Creasi\Nusa\Contracts\District $district
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait WithDistrict
{
    /**
     * Initialize the trait.
     */
    final protected function initializeWithDistrict(): void
    {
        $this->mergeCasts([
            $this->districtKeyName() => 'int',
        ]);

        $this->mergeFillable([
            $this->districtKeyName(),
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function district()
    {
        return $this->belongsTo(District::class, $this->districtKeyName());
    }

    protected function districtKeyName(): string
    {
        return \property_exists($this, 'districtKey') ? $this->districtKey : 'district_code';
    }
}
