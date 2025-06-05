<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Village as VillageContract;

class Village extends Model implements VillageContract
{
    /** @use Concerns\WithDistrict<static> */
    use Concerns\WithDistrict;

    /** @use Concerns\WithProvince<static> */
    use Concerns\WithProvince;

    /** @use Concerns\WithRegency<static> */
    use Concerns\WithRegency;

    protected $fillable = ['postal_code'];

    protected $casts = [
        'postal_code' => 'int',
    ];

    public function getTable()
    {
        return config('creasi.nusa.table_names.villages', parent::getTable());
    }
}
