<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Regency as RegencyContract;
use Creasi\Nusa\Models\Concerns\WithProvince;

/**
 * @property-read Province $province
 * @property-read \Illuminate\Database\Eloquent\Collection<int, District> $districts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Village> $villages
 */
class Regency extends Model implements RegencyContract
{
    use WithProvince;

    protected $fillable = [];

    protected $casts = [];

    public function getTable()
    {
        return config('creasi.nusa.table_names.regencies', parent::getTable());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|District
     */
    public function districts()
    {
        return $this->hasMany(District::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|Village
     */
    public function villages()
    {
        return $this->hasMany(Village::class);
    }
}
