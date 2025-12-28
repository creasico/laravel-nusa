<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property-read null|Address $address
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface HasAddress
{
    /**
     * @return MorphOne<Address, $this>
     */
    public function address(): MorphOne;
}
