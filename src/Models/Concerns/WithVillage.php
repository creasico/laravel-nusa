<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Creasi\Nusa\Models\Village;

/**
 * @property-read Village|\Creasi\Nusa\Contracts\Village $village
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait WithVillage
{
    /**
     * Initialize the trait.
     */
    final protected function initializeWithVillage(): void
    {
        $this->mergeFillable([
            $this->villageKeyName(),
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\Creasi\Nusa\Contracts\Village
     */
    public function village()
    {
        return $this->belongsTo(Village::class, $this->villageKeyName());
    }

    protected function villageKeyName(): string
    {
        return \property_exists($this, 'villageKey') ? $this->villageKey : 'village_code';
    }
}
