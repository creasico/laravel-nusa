<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

/**
 * @property-read string $code
 * @property-read string $regency_code
 * @property-read string $province_code
 * @property-read string $name
 */
class District extends Model
{
    protected $fillable = ['code', 'regency_code', 'province_code', 'name'];

    protected $casts = [];

    public function getTable()
    {
        return config('creasi.nusa.table_names.districts', parent::getTable());
    }
}
