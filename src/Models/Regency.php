<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Regency as RegencyContract;
use Creasi\Nusa\Models\Concerns\WithDistricts;
use Creasi\Nusa\Models\Concerns\WithProvince;
use Creasi\Nusa\Models\Concerns\WithVillages;

/**
 * @property-read Province $province
 * @property-read \Illuminate\Database\Eloquent\Collection<int, District> $districts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Village> $villages
 */
class Regency extends Model implements RegencyContract
{
    use WithDistricts;
    use WithProvince;
    use WithVillages;

    protected $fillable = [];

    protected $casts = [];

    public function getTable()
    {
        return config('creasi.nusa.table_names.regencies', parent::getTable());
    }

    public function coordinates()
    {
        return $this->hasMany(Coordinate::class);
    }
}
