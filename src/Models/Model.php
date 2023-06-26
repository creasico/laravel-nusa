<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Model as ModelContract;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @mixin \Illuminate\Contracts\Database\Eloquent\Builder
 */
abstract class Model extends EloquentModel implements ModelContract
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
            'code' => 'int',
            'latitude' => 'float',
            'longitude' => 'float',
            'coordinates' => 'array',
        ]);
    }

    public function getFillable()
    {
        return \array_merge($this->fillable, ['code', 'name', 'latitude', 'longitude', 'coordinates']);
    }
}
