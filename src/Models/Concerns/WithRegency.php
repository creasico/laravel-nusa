<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Creasi\Nusa\Models\Regency;

/**
 * @property-read Regency|\Creasi\Nusa\Contracts\Regency $regency
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait WithRegency
{
    /**
     * Initialize the trait.
     */
    final protected function initializeWithRegency(): void
    {
        $this->mergeCasts([
            $this->regencyKeyName() => 'int',
        ]);

        $this->mergeFillable([
            $this->regencyKeyName(),
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function regency()
    {
        return $this->belongsTo(Regency::class, $this->regencyKeyName());
    }

    protected function regencyKeyName(): string
    {
        return \property_exists($this, 'regencyKey') ? $this->regencyKey : 'regency_code';
    }
}
