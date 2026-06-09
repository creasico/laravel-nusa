<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Creasi\Nusa\Contracts\HasCoordinate;

/**
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
 * @mixin HasCoordinate
 */
trait WithCoordinate
{
    /**
     * Initialize the trait.
     */
    final protected function initializeWithCoordinate(): void
    {
        $this->mergeCasts([
            'latitude' => 'float',
            'longitude' => 'float',
            'coordinates' => 'array',
        ]);

        $this->mergeFillable([
            'latitude',
            'longitude',
            'coordinates',
        ]);
    }
}
