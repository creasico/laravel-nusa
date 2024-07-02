<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Province as ProvinceContract;
use Creasi\Nusa\Models\Concerns\WithDistricts;
use Creasi\Nusa\Models\Concerns\WithVillages;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Regency> $regencies
 */
class Province extends Model implements ProvinceContract
{
    use WithDistricts;
    use WithVillages;

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

    public function coordinates()
    {
        return $this->hasMany(Coordinate::class);
    }
}
