<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Province as ProvinceContract;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Regency> $regencies
 * @property-read \Illuminate\Database\Eloquent\Collection<int, District> $districts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Village> $villages
 */
class Province extends Model implements ProvinceContract
{
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
