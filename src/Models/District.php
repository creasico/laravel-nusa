<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

/**
 * @property-read string $code
 * @property-read string $regency_code
 * @property-read string $province_code
 * @property-read string $name
 * @property-read Province $province
 * @property-read Regency $regency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Village>|Village[] $villages
 */
class District extends Model
{
    protected $fillable = ['code', 'regency_code', 'province_code', 'name'];

    protected $casts = [];

    public function getTable()
    {
        return config('creasi.nusa.table_names.districts', parent::getTable());
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function regency()
    {
        return $this->belongsTo(Regency::class);
    }

    public function villages()
    {
        return $this->hasMany(Village::class);
    }
}
