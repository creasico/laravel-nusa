<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

/**
 * @property-read string $code
 * @property-read string $province_code
 * @property-read string $name
 * @property-read Province $province
 * @property-read \Illuminate\Database\Eloquent\Collection<int, District>|District[] $districts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Village>|Village[] $villages
 */
class Regency extends Model
{
    protected $fillable = ['code', 'province_code', 'name'];

    protected $casts = [];

    public function getTable()
    {
        return config('creasi.nusa.table_names.regencies', parent::getTable());
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
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
