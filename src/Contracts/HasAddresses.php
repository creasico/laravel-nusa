<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Address> $addresses
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface HasAddresses
{
    /**
     * @return MorphMany<Address, $this>
     */
    public function addresses(): MorphMany;
}
