<?php

declare(strict_types=1);

namespace Creasi\Nusa\Support;

trait GeometryHelpers
{
    /**
     * @throws \InvalidArgumentException
     */
    private function formatGeoJson(string $kind, string $code, string $name, float $longitude, float $latitude, array $coordinates): array
    {
        if (empty($coordinates)) {
            // @codeCoverageIgnoreStart
            throw new \InvalidArgumentException('Coordinates array cannot be empty');
            // @codeCoverageIgnoreEnd
        }

        $depth = 0;
        $children = $coordinates;
        $properties = [
            'code' => $code,
            'kind' => $kind,
            'name' => $name,
        ];

        while (is_array($children) && isset($children[0])) {
            $depth++;
            $children = $children[0];
        }

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
                        'type' => match ($depth) {
                            3 => 'Polygon',
                            4 => 'MultiPolygon',
                            default => throw new \InvalidArgumentException("Unsupported coordinate depth: {$depth}"),
                        },
                        'coordinates' => $coordinates,
                    ],
                ],
            ],
        ];
    }
}
