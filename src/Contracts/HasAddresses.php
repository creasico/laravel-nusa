<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Creasi\Nusa\Models\Address;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @template TModel of Model
 *
 * @property-read Collection<int, Address> $addresses
 *
 * @mixin TModel
 */
interface HasAddresses
{
    /**
     * @return MorphMany<Address, TModel>
     */
    public function addresses(): MorphMany;
}
