<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read int $id
 * @property string $line
 * @property null|int $village_code
 * @property null|int $district_code
 * @property null|int $regency_code
 * @property null|int $province_code
 * @property null|int $postal_code
 * @property-read null|\Illuminate\Database\Eloquent\Model $addressable
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface Address
{
    /**
     * @return MorphTo<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function addressable(): MorphTo;
}
