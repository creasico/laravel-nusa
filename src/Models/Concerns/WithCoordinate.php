<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

/**
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
 * @mixin \Creasi\Nusa\Contracts\HasCoordinate
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
        ]);

        $this->mergeFillable([
            'latitude',
            'longitude',
        ]);
    }
}
