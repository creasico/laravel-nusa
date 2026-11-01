<?php

declare(strict_types=1);

use Creasi\Tests\Feature\TestCase;
use Creasi\Tests\Fixture\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    /** @var TestCase $this */
    $this->path = 'nusa';

    $connection = DB::connection(config('creasi.nusa.connection'));
    $connection->beginTransaction();

    $coordinates = json_encode([[[110, -7], [111, -7], [111, -8], [110, -7]]]);

    $connection->table('provinces')->insert([
        ['code' => '98', 'name' => 'Remote Province', 'latitude' => 0, 'longitude' => 0, 'coordinates' => null],
        ['code' => '99', 'name' => 'Local Province', 'latitude' => -7, 'longitude' => 110, 'coordinates' => $coordinates],
    ]);
    $connection->table('regencies')->insert([
        ['code' => '99.99', 'province_code' => '99', 'name' => 'Local Regency', 'latitude' => -7, 'longitude' => 110, 'coordinates' => $coordinates],
    ]);
    $connection->table('districts')->insert([
        ['code' => '99.99.99', 'regency_code' => '99.99', 'province_code' => '99', 'name' => 'Local District', 'latitude' => -7, 'longitude' => 110, 'coordinates' => $coordinates],
    ]);
    $connection->table('villages')->insert([
        ['code' => '99.99.99.9999', 'district_code' => '99.99.99', 'regency_code' => '99.99', 'province_code' => '99', 'name' => 'Local Village', 'postal_code' => '99999', 'latitude' => -7, 'longitude' => 110, 'coordinates' => $coordinates],
    ]);
});

afterEach(function () {
    DB::connection(config('creasi.nusa.connection'))->rollBack();
});

it('formats geojson locally when coordinates exist', function () {
    /** @var TestCase $this */
    $response = $this->get($this->path('99'), ['Accept' => 'application/geo+json']);

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/geo+json; charset=UTF-8')
        ->assertJsonStructure(ApiResponse::geoJsonFields())
        ->assertJsonPath('features.0.properties.kind', 'province')
        ->assertJsonPath('features.0.geometry.type', 'Point')
        ->assertJsonPath('features.1.geometry.type', 'Polygon')
        ->assertJsonPath('features.1.geometry.coordinates.0.0', [110, -7]);
});

it('proxies remote geojson when coordinates are missing', function () {
    /** @var TestCase $this */
    Http::fake(['*' => Http::response([
        'type' => 'FeatureCollection',
        'features' => [[
            'type' => 'Feature',
            'properties' => ['code' => '98', 'kind' => 'province', 'name' => 'Remote Province'],
            'geometry' => ['type' => 'Point', 'coordinates' => [0, 0]],
        ]],
    ], 200)]);

    $response = $this->get($this->path('98'), ['Accept' => 'application/geo+json']);

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/geo+json; charset=UTF-8')
        ->assertJsonStructure(ApiResponse::geoJsonFields());

    Http::assertSent(fn ($request) => str_contains($request->url(), '/98.geojson'));
});

it('returns 404 when remote geojson is missing', function () {
    /** @var TestCase $this */
    Http::fake(['*' => Http::response(['message' => 'not found'], 404)]);

    $this->get($this->path('98'), ['Accept' => 'application/geo+json'])
        ->assertNotFound()
        ->assertExactJson([
            'message' => 'The geojson for 98 could not be found.',
        ]);
});

it('formats village geojson locally', function () {
    /** @var TestCase $this */
    $response = $this->get($this->path('99/99/99/9999'), ['Accept' => 'application/geo+json']);

    $response->assertOk()
        ->assertJsonStructure(ApiResponse::geoJsonFields())
        ->assertJsonPath('features.0.properties.kind', 'village');
});
