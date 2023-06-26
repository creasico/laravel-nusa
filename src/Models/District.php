<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

/**
 * @property-read int $regency_code
 * @property-read int $province_code
 * @property-read Province $province
 * @property-read Regency $regency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Village>|Village[] $villages
 */
class District extends Model
{
    protected $fillable = ['code', 'regency_code', 'province_code', 'name'];

    protected $casts = [
        'regency_code' => 'int',
        'province_code' => 'int',
    ];

    public function getTable()
    {
        return config('creasi.nusa.table_names.districts', parent::getTable());
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|Village
     */
    public function villages()
    {
        return $this->hasMany(Village::class);
    }
}
