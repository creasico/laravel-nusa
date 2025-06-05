<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Creasi\Nusa\Models\Province;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
 * @property-read Province|\Creasi\Nusa\Contracts\Province $province
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait WithProvince
{
    /**
     * Initialize the trait.
     */
    final protected function initializeWithProvince(): void
    {
        $this->mergeFillable([
            $this->provinceKeyName(),
        ]);
    }

    /**
     * @return BelongsTo<Province, TDeclaringModel>
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, $this->provinceKeyName());
    }

    protected function provinceKeyName(): string
    {
        return \property_exists($this, 'provinceKey') ? $this->provinceKey : 'province_code';
    }
}
