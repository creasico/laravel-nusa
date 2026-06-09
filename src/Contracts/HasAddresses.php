<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property-read Collection<int, Address> $addresses
 *
 * @mixin Model
 */
interface HasAddresses
{
    /**
     * @return MorphMany<Address, $this>
     */
    public function addresses(): MorphMany;
}
