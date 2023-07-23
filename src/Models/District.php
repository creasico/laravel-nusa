<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\District as DistrictContract;
use Creasi\Nusa\Models\Concerns\WithProvince;
use Creasi\Nusa\Models\Concerns\WithRegency;
use Creasi\Nusa\Models\Concerns\WithVillages;

/**
 * @property-read Province $province
 * @property-read Regency $regency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Village> $villages
 */
class District extends Model implements DistrictContract
{
    use WithRegency;
    use WithProvince;
    use WithVillages;

    protected $fillable = [];

    protected $casts = [];

    public function getTable()
    {
        return config('creasi.nusa.table_names.districts', parent::getTable());
    }
}
