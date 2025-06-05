<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Province as ProvinceContract;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Regency> $regencies
 */
class Province extends Model implements ProvinceContract
{
    /** @use Concerns\WithDistricts<static> */
    use Concerns\WithDistricts;

    /** @use Concerns\WithVillages<static> */
    use Concerns\WithVillages;

    protected $fillable = [];

    protected $casts = [];

    public function getTable()
    {
        return config('creasi.nusa.table_names.provinces', parent::getTable());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|Regency
     */
    public function regencies()
    {
        return $this->hasMany(Regency::class);
    }
}
