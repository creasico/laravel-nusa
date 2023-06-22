<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

/**
 * @property-read string $code
 * @property-read string $province_code
 * @property-read string $name
 */
class Regency extends Model
{
    protected $fillable = ['code', 'province_code', 'name'];

    protected $casts = [];

    public function getTable()
    {
        return config('creasi.nusa.table_names.regencies', parent::getTable());
    }
}
