<?php

declare(strict_types=1);

namespace Creasi\Tests\Fixture;

use Creasi\Nusa\Support\GeometryHelpers;

final class GeometryHelpersFixture
{
    use GeometryHelpers;

    /**
     * Format coordinates as a GeoJSON feature collection.
     */
    public function format(string $kind, string $code, string $name, float $longitude, float $latitude, array $coordinates): array
    {
        return $this->formatGeoJson($kind, $code, $name, $longitude, $latitude, $coordinates);
    }
}
