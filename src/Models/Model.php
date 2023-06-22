<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @mixin \Illuminate\Contracts\Database\Eloquent\Builder
 */
abstract class Model extends EloquentModel
{
    public $incrementing = false;

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public function getConnectionName()
    {
        return \config('creasi.nusa.connection');
    }
}
