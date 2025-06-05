<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Creasi\Nusa\Models\Village;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, int> $postal_codes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Village|\Creasi\Nusa\Contracts\Village> $villages
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait WithVillages
{
    /**
     * Initialize the trait.
     */
    final protected function initializeWithVillages(): void
    {
        $this->append('postal_codes');

        $this->makeHidden('distinctVillagesByPostalCodes');
    }

    /**
     * @return HasMany<Village, TDeclaringModel>
     */
    public function villages(): HasMany
    {
        return $this->hasMany(Village::class);
    }

    public function postalCodes(): Attribute
    {
        $this->loadMissing('distinctVillagesByPostalCodes');

        return Attribute::get(fn () => $this->distinctVillagesByPostalCodes->pluck('postal_code'));
    }

    public function distinctVillagesByPostalCodes()
    {
        return $this->villages()->distinct('postal_code')->groupBy('postal_code');
    }
}
