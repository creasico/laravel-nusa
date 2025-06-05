<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Creasi\Nusa\Models\District;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
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
        $this->mergeFillable([
            $this->districtKeyName(),
        ]);
    }

    /**
     * @return BelongsTo<District, TDeclaringModel>
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, $this->districtKeyName());
    }

    protected function districtKeyName(): string
    {
        return \property_exists($this, 'districtKey') ? $this->districtKey : 'district_code';
    }
}
