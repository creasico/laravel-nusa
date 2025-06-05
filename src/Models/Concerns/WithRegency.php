<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Creasi\Nusa\Models\Regency;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
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
        $this->mergeFillable([
            $this->regencyKeyName(),
        ]);
    }

    /**
     * @return BelongsTo<Regency, TDeclaringModel>
     */
    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, $this->regencyKeyName());
    }

    protected function regencyKeyName(): string
    {
        return \property_exists($this, 'regencyKey') ? $this->regencyKey : 'regency_code';
    }
}
