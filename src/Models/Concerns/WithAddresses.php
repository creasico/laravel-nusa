<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

/**
 * @mixin \Creasi\Nusa\Contracts\HasAddresses
 */
trait WithAddresses
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|\Creasi\Nusa\Contracts\Address
     */
    public function addresses()
    {
        return $this->morphMany(\config('creasi.nusa.addressable'), 'addressable');
    }
}
