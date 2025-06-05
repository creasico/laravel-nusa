<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 * @template TAddressableModel of \Creasi\Nusa\Contracts\Address
 *
 * @mixin \Creasi\Nusa\Contracts\HasAddresses
 */
trait WithAddresses
{
    /**
     * @return MorphMany<TAddressableModel, TDeclaringModel>
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(\config('creasi.nusa.addressable'), 'addressable');
    }
}
