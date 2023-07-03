<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Address>|Address[] $addresses
 */
interface Addressable
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|Address
     */
    public function addresses();
}
