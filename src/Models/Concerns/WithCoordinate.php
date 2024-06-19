<?php

declare(strict_types=1);

namespace Creasi\Nusa\Models\Concerns;

use Creasi\Nusa\Models\Coordinate;

/**
 * @mixin \Creasi\Nusa\Contracts\HasCoordinate
 */
trait WithCoordinate
{
    /**
     * Initialize the trait.
     */
    protected function initializeWithCoordinate(): void
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\Creasi\Nusa\Contracts\Coordinate
     */
    public function coordinate()
    {
        return $this->belongsTo(Coordinate::class);
    }
}
