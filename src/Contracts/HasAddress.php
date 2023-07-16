<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

/**
 * @property-read null|Address $address
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface HasAddress
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne|\Address
     */
    public function address();
}
