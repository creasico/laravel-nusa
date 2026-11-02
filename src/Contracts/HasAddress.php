<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Creasi\Nusa\Models\Address;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @template TModel of Model
 *
 * @property-read null|Address $address
 *
 * @mixin TModel
 */
interface HasAddress
{
    /**
     * @return MorphOne<Address, TModel>
     */
    public function address(): MorphOne;
}
