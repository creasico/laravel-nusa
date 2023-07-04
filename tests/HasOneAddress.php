<?php

declare(strict_types=1);

namespace Creasi\Tests;

use Creasi\Nusa\Support\HasAddress;
use Illuminate\Database\Eloquent\Model;

class HasOneAddress extends Model
{
    use HasAddress;

    public $timestamps = false;
}
