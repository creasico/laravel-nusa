<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 * @template TAddressableModel of \Creasi\Nusa\Contracts\Address
 *
 * @mixin \Creasi\Nusa\Contracts\HasAddress
 */
trait WithAddress
{
    /**
     * @return MorphOne<TAddressableModel, TDeclaringModel>
     */
    public function address(): MorphOne
    {
        return $this->morphOne(\config('creasi.nusa.addressable'), 'addressable');
    }
}
