<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Regency>|Regency[] $regencies
 * @property-read \Illuminate\Database\Eloquent\Collection<int, District>|District[] $districts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Village>|Village[] $villages
 */
class Province extends Model
{
    protected $fillable = ['code', 'name'];

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
