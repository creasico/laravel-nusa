<?php

declare(strict_types=1);

use Creasi\Nusa\Models\Village;
use Creasi\Tests\Feature\TestCase;
use Creasi\Tests\Fixture\ApiResponse;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\getJson;

beforeEach(function () {
    /**
     * @var TestCase $this
     *
     * @disregard P1056
     */
    $this->path = 'nusa';
});

function fakeGeoJson(): array
{
    return [
        'type' => 'FeatureCollection',
        'features' => [
            [
                'type' => 'Feature',
                'properties' => ['code' => '11', 'kind' => 'province', 'name' => 'Aceh'],
                'geometry' => ['type' => 'Point', 'coordinates' => [95.3, 5.5]],
            ],
        ],
    ];
}

it('lists all provinces as json', function (string $accept) {
    /** @var TestCase $this */
    $response = $this->get($this->path, ['Accept' => $accept]);

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/json')
        ->assertJsonStructure(['*' => ApiResponse::provinceFields()]);
})->with(ApiResponse::ACCEPT_HEADERS);

it('supports csv format via accept header', function () {
    /** @var TestCase $this */
    $response = $this->get($this->path, ['Accept' => 'text/csv']);

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

it('rejects an unacceptable index format', function () {
    /** @var TestCase $this */
    $this->get($this->path, ['Accept' => 'text/html'])->assertNotAcceptable();
});

it('returns 404 for an unknown province route', function () {
    /** @var TestCase $this */
    getJson($this->path('invalid'))
        ->assertNotFound()
        ->assertExactJson([
            'message' => 'The route nusa/invalid could not be found.',
        ]);
});

it('shows a province as json', function (string $accept) {
    /** @var TestCase $this */
    $response = $this->get($this->path('33'), ['Accept' => $accept]);

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/json')
        ->assertJsonStructure([
            ...ApiResponse::provinceFields(),
            'regencies' => ['*' => ApiResponse::provinceFields()],
        ])
        ->assertJsonPath('code', '33');
})->with(ApiResponse::ACCEPT_HEADERS);

it('shows a province as csv', function () {
    /** @var TestCase $this */
    $response = $this->get($this->path('33'), ['Accept' => 'text/csv']);

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

it('shows a province as geojson', function () {
    /** @var TestCase $this */
    Http::fake(['*' => Http::response(fakeGeoJson())]);

    $response = $this->get($this->path('33'), ['Accept' => 'application/geo+json']);

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/geo+json; charset=UTF-8')
        ->assertJsonStructure(ApiResponse::geoJsonFields());
});

it('rejects an unacceptable province format', function () {
    /** @var TestCase $this */
    $this->get($this->path('33'), ['Accept' => 'text/html'])->assertNotAcceptable();
});

it('returns 404 for an unknown regency route', function () {
    /** @var TestCase $this */
    getJson($this->path('invalid/invalid'))
        ->assertNotFound()
        ->assertExactJson([
            'message' => 'The route nusa/invalid/invalid could not be found.',
        ]);
});

it('shows a regency as json', function (string $accept) {
    /** @var TestCase $this */
    $response = $this->get($this->path('33/75'), ['Accept' => $accept]);

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/json')
        ->assertJsonStructure([
            ...ApiResponse::regencyFields(),
            'districts' => ['*' => ApiResponse::districtFields()],
        ])
        ->assertJsonPath('code', '33.75');
})->with(ApiResponse::ACCEPT_HEADERS);

it('shows a regency as csv', function () {
    /** @var TestCase $this */
    $response = $this->get($this->path('33/75'), ['Accept' => 'text/csv']);

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

it('shows a regency as geojson', function () {
    /** @var TestCase $this */
    Http::fake(['*' => Http::response(fakeGeoJson())]);

    $response = $this->get($this->path('33/75'), ['Accept' => 'application/geo+json']);

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/geo+json; charset=UTF-8')
        ->assertJsonStructure(ApiResponse::geoJsonFields());
});

it('rejects an unacceptable regency format', function () {
    /** @var TestCase $this */
    $this->get($this->path('33/75'), ['Accept' => 'text/html'])->assertNotAcceptable();
});

it('returns 404 for an unknown district route', function () {
    /** @var TestCase $this */
    getJson($this->path('invalid/invalid/invalid'))
        ->assertNotFound()
        ->assertExactJson([
            'message' => 'The route nusa/invalid/invalid/invalid could not be found.',
        ]);
});

it('shows a district as json', function (string $accept) {
    /** @var TestCase $this */
    $response = $this->get($this->path('33/75/03'), ['Accept' => $accept]);

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/json')
        ->assertJsonStructure([
            ...ApiResponse::districtFields(),
            'villages' => ['*' => ApiResponse::villageFields()],
        ])
        ->assertJsonPath('code', '33.75.03');
})->with(ApiResponse::ACCEPT_HEADERS);

it('shows a district as csv', function () {
    /** @var TestCase $this */
    $response = $this->get($this->path('33/75/03'), ['Accept' => 'text/csv']);

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

it('shows a district as geojson', function () {
    /** @var TestCase $this */
    Http::fake(['*' => Http::response(fakeGeoJson())]);

    $response = $this->get($this->path('33/75/03'), ['Accept' => 'application/geo+json']);

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/geo+json; charset=UTF-8')
        ->assertJsonStructure(ApiResponse::geoJsonFields());
});

it('rejects an unacceptable district format', function () {
    /** @var TestCase $this */
    $this->get($this->path('33/75/03'), ['Accept' => 'text/html'])->assertNotAcceptable();
});

it('returns 404 for an unknown village route', function () {
    /** @var TestCase $this */
    getJson($this->path('invalid/invalid/invalid/invalid'))
        ->assertNotFound()
        ->assertExactJson([
            'message' => 'The route nusa/invalid/invalid/invalid/invalid could not be found.',
        ]);
});

it('shows a village as json', function (string $accept) {
    /** @var TestCase $this */
    $response = $this->get($this->path('33/75/03/1006'), ['Accept' => $accept]);

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/json')
        ->assertJsonStructure(ApiResponse::villageFields())
        ->assertJsonPath('code', '33.75.03.1006');
})->with(ApiResponse::ACCEPT_HEADERS);

it('shows a village as geojson', function () {
    /** @var TestCase $this */
    Http::fake(['*' => Http::response(fakeGeoJson())]);

    $response = $this->get($this->path('33/75/03/1006'), ['Accept' => 'application/geo+json']);

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/geo+json; charset=UTF-8')
        ->assertJsonStructure(ApiResponse::geoJsonFields());
});

it('rejects an unacceptable village format', function () {
    /** @var TestCase $this */
    $this->get($this->path('33/75/03/1006'), ['Accept' => 'text/html'])->assertNotAcceptable();
});

it('returns 404 when a village has no geojson', function () {
    /** @var TestCase $this */
    $village = Village::query()->whereNull('coordinates')->first();

    if (! $village) {
        $this->markTestSkipped('No village without coordinates found.');
    }

    Http::fake(['*' => Http::response(['message' => "The geojson for {$village->code} could not be found."], 404)]);

    $path = str_replace('.', '/', $village->code);

    $this->get($this->path($path), ['Accept' => 'application/geo+json'])
        ->assertNotFound()
        ->assertExactJson([
            'message' => "The geojson for {$village->code} could not be found.",
        ]);
});
