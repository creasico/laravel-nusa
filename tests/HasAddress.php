<?php

declare(strict_types=1);

namespace Creasi\Tests;

use Creasi\Nusa\Support\HasAddresses;
use Illuminate\Database\Eloquent\Model;

class HasAddress extends Model
{
    use HasAddresses;

    public $timestamps = false;
}
