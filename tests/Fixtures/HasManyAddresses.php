<?php

declare(strict_types=1);

namespace Creasi\Tests\Fixtures;

use Creasi\Nusa\Contracts\HasAddresses;
use Creasi\Nusa\Models\Concerns\WithAddresses;
use Illuminate\Database\Eloquent\Model;

class HasManyAddresses extends Model implements HasAddresses
{
    use WithAddresses;

    public $timestamps = false;
}
