<?php

declare(strict_types=1);

namespace Creasi\Tests\Fixture;

use Creasi\Nusa\Contracts\Address;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class CustomAddress implements Address
{
    public function addressable(): MorphTo
    {
        throw new \LogicException('Not used in this test.');
    }
}
