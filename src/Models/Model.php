<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @property-read int $code
 * @property-read string $name
 * @property-read ?float $latitude
 * @property-read ?float $longintude
 * @property-read ?array $coordinates
 *
 * @mixin \Illuminate\Contracts\Database\Eloquent\Builder
 */
abstract class Model extends EloquentModel
{
    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = 'code';

    public function getConnectionName()
    {
        return \config('creasi.nusa.connection');
    }

    public function getCasts()
    {
        return \array_merge($this->casts, [
            'latitude' => 'float',
            'longitude' => 'float',
            'coordinates' => 'array',
        ]);
    }

    public function getFillable()
    {
        return \array_merge($this->fillable, ['latitude', 'longitude', 'coordinates']);
    }
}
