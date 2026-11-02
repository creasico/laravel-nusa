<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Creasi\Nusa\Contracts\HasCoordinate;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Collection;

/**
 * @property-read string $code
 * @property-read string $name
 * @property-read null|float $latitude
 * @property-read null|float $longitude
 * @property-read null|array<int, mixed> $coordinates
 * @property-read null|int $postal_code
 * @property-read string $district_code
 * @property-read string $regency_code
 * @property-read string $province_code
 * @property-read Collection<int, int> $postal_codes
 * @property-read Province $province
 * @property-read Regency $regency
 * @property-read District $district
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Village> $villages
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Regency> $regencies
 * @property-read \Illuminate\Database\Eloquent\Collection<int, District> $districts
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static> search(string $keyword)
 * @method \Illuminate\Database\Eloquent\Builder<static> search(string $keyword)
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
        return config('creasi.nusa.connection');
    }

    public function getFillable()
    {
        return array_merge(parent::getFillable(), ['code', 'name']);
    }

    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->whereRaw(match ($this->getConnection()->getDriverName()) {
            'pgsql' => 'name ilike ?',
            'mysql' => 'lower(name) like lower(?)',
            'sqlite' => 'name like ? COLLATE NOCASE',
            default => throw new \RuntimeException('Unsupported database driver.'),
        }, "%{$keyword}%");
    }
}
