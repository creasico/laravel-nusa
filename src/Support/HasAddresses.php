<?php

declare(strict_types=1);

namespace Creasi\Nusa\Support;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasAddresses
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|\Creasi\Nusa\Contracts\Address
     */
    public function addresses()
    {
        return $this->morphMany(\config('creasi.nusa.addressable'), 'owner');
    }
}
