<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Creasi\Nusa\Models\Province;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait WithProvince
{
    /**
     * Initialize the trait.
     */
    public function initializeWithProvince(): void
    {
        $this->mergeCasts([
            $this->provinceKeyName() => 'int',
        ]);

        $this->mergeFillable([
            $this->provinceKeyName(),
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\Creasi\Nusa\Contracts\Province
     */
    public function province()
    {
        return $this->belongsTo(Province::class, $this->provinceKeyName());
    }

    protected function provinceKeyName(): string
    {
        return \property_exists($this, 'provinceKey') ? $this->provinceKey : 'province_code';
    }
}
