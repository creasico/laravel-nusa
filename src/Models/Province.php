<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Province as ProvinceContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Regency> $regencies
 */
class Province extends Model implements ProvinceContract
{
    /** @use Concerns\WithDistricts<static> */
    use Concerns\WithDistricts;

    /** @use Concerns\WithVillages<static> */
    use Concerns\WithVillages;

    public function getTable()
    {
        return config('creasi.nusa.table_names.provinces', parent::getTable());
    }

    /**
     * @see ProvinceContract::regencies()
     *
     * @return HasMany<Regency, $this>
     */
    public function regencies(): HasMany
    {
        return $this->hasMany(Regency::class);
    }

    /**
     * @return Collection<int, \Creasi\Nusa\Contracts\Regency>
     */
    public function subdivisions(): Collection
    {
        return $this->regencies;
    }
}
