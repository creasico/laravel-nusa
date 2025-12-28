<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\District as DistrictContract;
use Illuminate\Database\Eloquent\Collection;

final class District extends Model implements DistrictContract
{
    /** @use Concerns\WithProvince<static> */
    use Concerns\WithProvince;

    /** @use Concerns\WithRegency<static> */
    use Concerns\WithRegency;

    /** @use Concerns\WithVillages<static> */
    use Concerns\WithVillages;

    public function getTable()
    {
        return config('creasi.nusa.table_names.districts', parent::getTable());
    }

    /**
     * @return Collection<int, \Creasi\Nusa\Contracts\Village>
     */
    public function subdivisions(): Collection
    {
        return $this->villages;
    }
}
