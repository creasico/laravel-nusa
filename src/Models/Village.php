<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Village as VillageContract;
use Creasi\Nusa\Models\Concerns\WithCoordinate;
use Creasi\Nusa\Models\Concerns\WithDistrict;
use Creasi\Nusa\Models\Concerns\WithProvince;
use Creasi\Nusa\Models\Concerns\WithRegency;

/**
 * @property-read Province $province
 * @property-read Regency $regency
 * @property-read District $district
 */
class Village extends Model implements VillageContract
{
    use WithDistrict;
    use WithProvince;
    use WithRegency;
    use WithCoordinate;

    protected $fillable = ['postal_code'];

    protected $casts = [
        'postal_code' => 'int',
    ];

    public function getTable()
    {
        return config('creasi.nusa.table_names.villages', parent::getTable());
    }
}
