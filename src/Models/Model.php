<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\HasCoordinate;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @property-read string $code
 * @property-read string $name
 *
 * @method static static search(string $keyword)
 * @method Builder whereCode(string $code)
 * @method Builder whereName(string $name)
 *
 * @mixin \Illuminate\Contracts\Database\Eloquent\Builder
 */
abstract class Model extends EloquentModel implements HasCoordinate
{
    use Concerns\WithCoordinate;

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public function getConnectionName()
    {
        return \config('creasi.nusa.connection');
    }

    public function getFillable()
    {
        return \array_merge(parent::getFillable(), ['code', 'name']);
    }

    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->whereRaw(match ($this->getConnection()->getDriverName()) {
            'pgsql' => 'name ilike ?',
            'mysql' => 'lower(name) like lower(?)',
            'sqlite' => 'name like ? COLLATE NOCASE',
        }, "%{$keyword}%");
    }
}
