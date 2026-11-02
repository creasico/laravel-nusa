<?php

declare(strict_types=1);

use Creasi\Tests\Fixture\GeometryHelpersFixture;
use Creasi\Tests\TestCase;

/**
 * Build a fixture using the GeometryHelpers trait.
 */
function geometry(): GeometryHelpersFixture
{
    return new GeometryHelpersFixture;
}

it('formats polygon coordinates as geojson', function () {
    /** @var TestCase $this */
    $geojson = geometry()->format(
        'district',
        '33.75.03',
        'Pekalongan Utara',
        109.675,
        -6.88,
        [[[109.675, -6.88], [109.7, -6.88], [109.7, -6.9], [109.675, -6.88]]],
    );

    expect($geojson['type'])->toBe('FeatureCollection')
        ->and($geojson['features'])->toHaveCount(2)
        ->and($geojson['features'][0]['geometry']['type'])->toBe('Point')
        ->and($geojson['features'][1]['geometry']['type'])->toBe('Polygon')
        ->and($geojson['features'][0]['properties']['kind'])->toBe('district');
});

it('formats multipolygon coordinates as geojson', function () {
    /** @var TestCase $this */
    $geojson = geometry()->format(
        'province',
        '33',
        'Jawa Tengah',
        110.0,
        -7.0,
        [[[[110.0, -7.0], [110.1, -7.0], [110.1, -7.1], [110.0, -7.0]]]],
    );

    expect($geojson['features'][1]['geometry']['type'])->toBe('MultiPolygon');
});

it('rejects empty coordinates', function () {
    /** @var TestCase $this */
    geometry()->format('province', '33', 'Jawa Tengah', 110.0, -7.0, []);
})->throws(InvalidArgumentException::class, 'Coordinates array cannot be empty');

it('rejects unsupported coordinate depth', function () {
    /** @var TestCase $this */
    geometry()->format('province', '33', 'Jawa Tengah', 110.0, -7.0, [[110.0, -7.0]]);
})->throws(InvalidArgumentException::class, 'Unsupported coordinate depth: 2');
