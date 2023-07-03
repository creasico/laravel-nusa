<?php

declare(strict_types=1);

namespace Creasi\Nusa\Support;

use Creasi\Nusa\Models\Address;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasAddresses
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|Address
     */
    public function addresses()
    {
        return $this->morphMany(Address::class, 'owner');
    }
}
