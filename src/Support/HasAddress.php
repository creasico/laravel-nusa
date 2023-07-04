<?php

declare(strict_types=1);

namespace Creasi\Nusa\Support;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasAddress
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne|\Creasi\Nusa\Contracts\Address
     */
    public function address()
    {
        return $this->morphOne(\config('creasi.nusa.addressable'), 'owner');
    }
}
