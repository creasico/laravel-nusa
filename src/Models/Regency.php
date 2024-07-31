<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Regency as RegencyContract;
use Creasi\Nusa\Models\Concerns\WithDistricts;
use Creasi\Nusa\Models\Concerns\WithProvince;
use Creasi\Nusa\Models\Concerns\WithVillages;

class Regency extends Model implements RegencyContract
{
    use WithDistricts;
    use WithProvince;
    use WithVillages;

    protected $fillable = [];

    protected $casts = [];

    public function getTable()
    {
        return config('creasi.nusa.table_names.regencies', parent::getTable());
    }
}
