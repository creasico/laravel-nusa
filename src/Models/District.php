<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\District as DistrictContract;
use Creasi\Nusa\Models\Concerns\WithProvince;
use Creasi\Nusa\Models\Concerns\WithRegency;

/**
 * @property-read Province $province
 * @property-read Regency $regency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Village> $villages
 */
class District extends Model implements DistrictContract
{
    use WithRegency;
    use WithProvince;

    protected $fillable = [];

    protected $casts = [];

    public function getTable()
    {
        return config('creasi.nusa.table_names.districts', parent::getTable());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|Village
     */
    public function villages()
    {
        return $this->hasMany(Village::class);
    }
}
