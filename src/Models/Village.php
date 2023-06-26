<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

/**
 * @property-read int $district_code
 * @property-read int $regency_code
 * @property-read int $province_code
 * @property-read int $postal_code
 * @property-read Province $province
 * @property-read Regency $regency
 * @property-read District $district
 */
class Village extends Model
{
    protected $fillable = ['code', 'district_code', 'regency_code', 'province_code', 'name', 'postal_code'];

    protected $casts = [
        'district_code' => 'int',
        'regency_code' => 'int',
        'province_code' => 'int',
        'postal_code' => 'int',
    ];

    public function getTable()
    {
        return config('creasi.nusa.table_names.villages', parent::getTable());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Province
     */
    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Regency
     */
    public function regency()
    {
        return $this->belongsTo(Regency::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|District
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
