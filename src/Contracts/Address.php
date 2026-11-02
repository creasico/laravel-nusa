<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @template TModel of Model
 *
 * @property-read int $id
 * @property null|int $village_code
 * @property null|int $district_code
 * @property null|int $regency_code
 * @property null|int $province_code
 * @property null|int $postal_code
 * @property-read null|Model $addressable
 *
 * @mixin TModel
 */
interface Address
{
    /**
     * @return MorphTo<Model, TModel>
     */
    public function addressable(): MorphTo;
}
