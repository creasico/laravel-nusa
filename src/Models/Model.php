<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\Model as ModelContract;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @method static static search(string|int $keyword)
 * @method Builder whereCode(int $code)
 * @method Builder whereName(string $name)
 *
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

    public function scopeSearch(Builder $query, string|int $keyword)
    {
        if (\is_numeric($keyword)) {
            return $query->where('code', $keyword);
        }

        return $query->whereRaw(match ($this->getConnection()->getDriverName()) {
            'pgsql' => 'name ilike ?',
            'mysql' => 'lower(name) like lower(?)',
            'sqlite' => 'name like ? COLLATE NOCASE',
        }, "%{$keyword}%");
    }
}
