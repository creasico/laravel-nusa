<?php

declare(strict_types=1);

use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;
use Creasi\Nusa\Models\Village;
use Creasi\Tests\Feature\TestCase;
use Creasi\Tests\Fixture\ApiResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\getJson;

beforeEach(function () {
    /**
     * @var TestCase $this
     *
     * @disregard P1056
     */
    $this->path = 'nusa/provinces';
});

dataset('availableQueries', fn () => [
    'basic request' => [],
    'include postal_codes' => ['postal_codes'],
    'include coordinates' => ['coordinates'],
    'include regencies' => ['regencies'],
    'include districts' => ['districts'],
    'include villages' => ['villages'],
]);

dataset('possibleSearchRegencies', fn () => [
    'no search' => [null],
    'with keyword' => ['pekalongan'],
]);

dataset('possibleSearchDistricts', fn () => [
    'no search' => [null],
    'with keyword' => ['kedung'],
]);

dataset('possibleSearchVillages', fn () => [
    'no search' => [null],
    'with keyword' => ['dukuh'],
]);

it('lists provinces', function (?string ...$with) {
    /** @var TestCase $this */
    $response = getJson($this->path(query: ['with' => $with, 'per-page' => 5]));

    $this->assertCollectionResponse($response, ApiResponse::provinceFields(), $with);
})->with('availableQueries');

it('rejects invalid codes', function (mixed $codes) {
    /** @var TestCase $this */
    $response = getJson($this->path(query: ['codes' => $codes]));

    $response->assertUnprocessable();
})->with(['non-array of numeric code' => ['33']]);

it('filters provinces by selected codes', function () {
    /** @var TestCase $this */
    $response = getJson($this->path(query: ['codes' => ['33', '32']]));

    $this->assertCollectionResponse($response, ApiResponse::provinceFields())
        ->assertJsonCount(2, 'data');
});

it('searches provinces by name', function () {
    /** @var TestCase $this */
    $expected = Province::search('Jawa Tengah')->count();

    $response = getJson($this->path(query: ['search' => 'Jawa Tengah']));

    $this->assertCollectionResponse($response, ApiResponse::provinceFields());
    $this->assertPaginatedTotal($response, $expected);
});

it('shows a single province', function (?string ...$with) {
    /** @var TestCase $this */
    $response = getJson($this->path('33', ['with' => $with]));

    $this->assertSingleResponse($response, ApiResponse::provinceFields(), $with)
        ->assertJsonPath('data.code', '33');
})->with('availableQueries');

it('returns 404 for an unknown province', function () {
    /** @var TestCase $this */
    getJson($this->path('99'))->assertNotFound();
});

it('lists regencies in a province', function (?string $search) {
    /** @var TestCase $this */
    $expected = Regency::where('province_code', '33')
        ->when($search, fn ($query) => $query->search($search))
        ->count();

    $response = getJson($this->path('33/regencies', ['search' => $search]));

    $this->assertCollectionResponse($response, ApiResponse::regencyFields());
    $this->assertPaginatedTotal($response, $expected);
})->with('possibleSearchRegencies');

it('lists districts in a province', function (?string $search) {
    /** @var TestCase $this */
    $expected = District::where('province_code', '33')
        ->when($search, fn ($query) => $query->search($search))
        ->count();

    $response = getJson($this->path('33/districts', ['search' => $search]));

    $this->assertCollectionResponse($response, ApiResponse::districtFields());
    $this->assertPaginatedTotal($response, $expected);
})->with('possibleSearchDistricts');

it('lists villages in a province', function (?string $search) {
    /** @var TestCase $this */
    $expected = Village::where('province_code', '33')
        ->when($search, fn ($query) => $query->search($search))
        ->count();

    $response = getJson($this->path('33/villages', ['search' => $search]));

    $this->assertCollectionResponse($response, ApiResponse::villageFields());
    $this->assertPaginatedTotal($response, $expected);
})->with('possibleSearchVillages');

it('ignores unknown relations', function () {
    /** @var TestCase $this */
    $response = getJson($this->path('33', ['with' => ['does_not_exist']]));

    $response->assertOk()->assertJsonMissingPath('data.does_not_exist');
});

it('includes relations in a nested list', function () {
    /** @var TestCase $this */
    $response = getJson($this->path('33/regencies', ['with' => ['province'], 'per-page' => 2]));

    $this->assertCollectionResponse($response, ApiResponse::regencyFields(), ['province']);
});

it('batches postal codes for included relations', function () {
    /** @var TestCase $this */
    Cache::flush();

    $queries = 0;
    DB::connection(config('creasi.nusa.connection'))->listen(function () use (&$queries) {
        $queries++;
    });

    $response = getJson($this->path(query: [
        'with' => ['regencies', 'postal_codes'],
        'per-page' => 2,
    ]));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [[
                'regencies' => [['postal_codes']],
                'postal_codes',
            ]],
        ]);

    expect($queries)->toBeLessThan(15);
});
