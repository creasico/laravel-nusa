<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

/**
 * @property-read string $code
 * @property-read string $district_code
 * @property-read string $regency_code
 * @property-read string $province_code
 * @property-read string $name
 * @property-read Province $province
 * @property-read Regency $regency
 * @property-read District $district
 */
class Village extends Model
{
    protected $fillable = ['code', 'district_code', 'regency_code', 'province_code', 'name'];

    protected $casts = [];

    public function getTable()
    {
        return config('creasi.nusa.table_names.villages', parent::getTable());
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function regency()
    {
        return $this->belongsTo(Regency::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
