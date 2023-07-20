<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

/**
 * @mixin \Creasi\Nusa\Contracts\HasAddress
 */
trait WithAddress
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne|\Creasi\Nusa\Contracts\Address
     */
    public function address()
    {
        return $this->morphOne(\config('creasi.nusa.addressable'), 'addressable');
    }
}
