<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Creasi\Nusa\Models\Village;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
 * @property-read Collection<int, int> $postal_codes
 * @property-read Collection<int, Village|\Creasi\Nusa\Contracts\Village> $villages
 * @property-read Collection<int, Village|\Creasi\Nusa\Contracts\Village> $distinctVillagesByPostalCodes
 *
 * @mixin Model
 */
trait WithVillages
{
    /**
     * Initialize the trait.
     */
    final protected function initializeWithVillages(): void
    {
        $this->makeHidden('distinctVillagesByPostalCodes');
    }

    /**
     * @return HasMany<Village, TDeclaringModel>
     */
    public function villages(): HasMany
    {
        return $this->hasMany(Village::class);
    }

    /**
     * @return HasMany<Village, TDeclaringModel>
     */
    public function distinctVillagesByPostalCodes(): HasMany
    {
        $relation = $this->villages();

        return $relation
            ->select('postal_code', $relation->getForeignKeyName())
            ->distinct();
    }

    public function postalCodes(): Attribute
    {
        $this->loadMissing('distinctVillagesByPostalCodes');

        return Attribute::get(fn () => Cache::flexible(
            'postal_codes:'.$this->getKey(),
            [5, 10],
            fn () => $this->distinctVillagesByPostalCodes->pluck('postal_code')
        ));
    }
}
