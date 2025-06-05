<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Regency as RegencyContract;

class Regency extends Model implements RegencyContract
{
    /** @use Concerns\WithPWithDistrictsrovince<static> */
    use Concerns\WithDistricts;

    /** @use Concerns\WithProvince<static> */
    use Concerns\WithProvince;

    /** @use Concerns\WithVillages<static> */
    use Concerns\WithVillages;

    protected $fillable = [];

    protected $casts = [];

    public function getTable()
    {
        return config('creasi.nusa.table_names.regencies', parent::getTable());
    }
}
