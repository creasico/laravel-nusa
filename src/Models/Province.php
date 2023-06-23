<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

/**
 * @property-read int $code
 * @property-read string $name
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

    public function regencies()
    {
        return $this->hasMany(Regency::class);
    }

    public function districts()
    {
        return $this->hasMany(District::class);
    }

    public function villages()
    {
        return $this->hasMany(Village::class);
    }
}
