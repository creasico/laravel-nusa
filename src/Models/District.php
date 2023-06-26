<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\District as DistrictContract;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Village> $villages
 */
class District extends Model implements DistrictContract
{
    protected $fillable = ['regency_code', 'province_code'];

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
