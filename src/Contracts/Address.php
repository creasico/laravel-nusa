<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @template TModel of Model
 *
 * @property-read int $id
 * @property null|string $village_code
 * @property null|string $district_code
 * @property null|string $regency_code
 * @property null|string $province_code
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
