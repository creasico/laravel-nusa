<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Regency as RegencyContract;
use Illuminate\Database\Eloquent\Collection;

class Regency extends Model implements RegencyContract
{
    /** @use Concerns\WithDistricts<static> */
    use Concerns\WithDistricts;

    /** @use Concerns\WithProvince<static> */
    use Concerns\WithProvince;

    /** @use Concerns\WithVillages<static> */
    use Concerns\WithVillages;

    public function getTable()
    {
        return config('creasi.nusa.table_names.regencies', parent::getTable());
    }

    /**
     * @return Collection<int, \Creasi\Nusa\Contracts\District>
     */
    public function subdivisions(): Collection
    {
        return $this->districts;
    }
}
