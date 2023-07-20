<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

/**
 * @mixin \Creasi\Nusa\Contracts\HasCoordinate
 */
trait WithCoordinate
{
    /**
     * Initialize the trait.
     */
    public function initializeWithCoordinate(): void
    {
        $this->mergeCasts([
            'latitude' => 'float',
            'longitude' => 'float',
        ]);

        $this->mergeFillable([
            'latitude',
            'longitude'
        ]);
    }
}
