<?php

declare(strict_types=1);

namespace Creasi\Tests\Fixtures;

use Creasi\Nusa\Contracts\HasAddress;
use Creasi\Nusa\Models\Concerns\WithAddress;
use Illuminate\Database\Eloquent\Model;

class HasOneAddress extends Model implements HasAddress
{
    use WithAddress;

    public $timestamps = false;
}
