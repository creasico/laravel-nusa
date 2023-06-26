<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Regency as RegencyContract;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, District> $districts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Village> $villages
 */
class Regency extends Model implements RegencyContract
{
    protected $fillable = ['province_code'];

    protected $casts = [
        'province_code' => 'int',
    ];

    public function getTable()
    {
        return config('creasi.nusa.table_names.regencies', parent::getTable());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Province
     */
    public function province()
    {
        return $this->belongsTo(Province::class);
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
