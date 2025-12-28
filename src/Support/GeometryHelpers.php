<?php

declare(strict_types=1);

namespace Creasi\Nusa\Support;

trait GeometryHelpers
{
    /**
     * Retrieve geometry type.
     *
     * @return 'Polygon'|'MultiPolygon'
     *
     * @throws \InvalidArgumentException
     */
    private function getGeometryType(array $coordinates): string
    {
        if (empty($coordinates)) {
            throw new \InvalidArgumentException('Coordinates array cannot be empty');
        }

        $depth = 0;
        $children = $coordinates;

        while (is_array($children) && isset($children[0])) {
            $depth++;
            $children = $children[0];
        }

        return match ($depth) {
            3 => 'Polygon',
            4 => 'MultiPolygon',
            default => throw new \InvalidArgumentException("Unsupported coordinate depth: {$depth}"),
        };
    }

    private function formatGeoJson(string $kind, string $code, string $name, float $longitude, float $latitude, array $coordinates): array
    {
        $properties = [
            'code' => $code,
            'kind' => $kind,
            'name' => $name,
        ];

        return [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => $properties,
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$longitude, $latitude],
                    ],
                ],
                [
                    'type' => 'Feature',
                    'properties' => $properties,
                    'geometry' => [
                        'type' => $this->getGeometryType($coordinates),
                        'coordinates' => $coordinates,
                    ],
                ],
            ],
        ];
    }
}
