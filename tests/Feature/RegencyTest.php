<?php

declare(strict_types=1);

use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Regency;
use Creasi\Nusa\Models\Village;
use Creasi\Tests\Feature\TestCase;
use Creasi\Tests\Fixture\ApiResponse;

use function Pest\Laravel\getJson;

beforeEach(function () {
    /**
     * @var TestCase $this
     *
     * @disregard P1056
     */
    $this->path = 'nusa/regencies';
});

dataset('availableQueries', fn () => [
    'basic request' => [],
    'include postal_codes' => ['postal_codes'],
    'include coordinates' => ['coordinates'],
    'include province' => ['province'],
    'include districts' => ['districts'],
    'include villages' => ['villages'],
]);

dataset('possibleSearchDistricts', fn () => [
    'no search' => [null],
    'with keyword' => ['kedung'],
]);

dataset('possibleSearchVillages', fn () => [
    'no search' => [null],
    'with keyword' => ['dukuh'],
]);

it('lists regencies', function (?string ...$with) {
    /** @var TestCase $this */
    $response = getJson($this->path(query: ['with' => $with]));

    $this->assertCollectionResponse($response, ApiResponse::regencyFields(), $with);
})->with('availableQueries');

it('rejects invalid codes', function (mixed $codes) {
    /** @var TestCase $this */
    $response = getJson($this->path(query: ['codes' => $codes]));

    $response->assertUnprocessable();
})->with(['non-array of numeric code' => ['33.75']]);

it('filters regencies by selected codes', function () {
    /** @var TestCase $this */
    $response = getJson($this->path(query: ['codes' => ['33.75', '33.25']]));

    $this->assertCollectionResponse($response, ApiResponse::regencyFields())
        ->assertJsonCount(2, 'data');
});

it('searches regencies by name', function () {
    /** @var TestCase $this */
    $expected = Regency::search('Pekalongan')->count();

    $response = getJson($this->path(query: ['search' => 'Pekalongan']));

    $this->assertCollectionResponse($response, ApiResponse::regencyFields());
    $this->assertPaginatedTotal($response, $expected);
});

it('shows a single regency', function (?string ...$with) {
    /** @var TestCase $this */
    $response = getJson($this->path('33.75', ['with' => $with]));

    $this->assertSingleResponse($response, ApiResponse::regencyFields(), $with)
        ->assertJsonPath('data.code', '33.75');
})->with('availableQueries');

it('returns 404 for an unknown regency', function () {
    /** @var TestCase $this */
    getJson($this->path('99.99'))->assertNotFound();
});

it('lists districts in a regency', function (?string $search) {
    /** @var TestCase $this */
    $expected = District::where('regency_code', '33.26')
        ->when($search, fn ($query) => $query->search($search))
        ->count();

    $response = getJson($this->path('33.26/districts', ['search' => $search]));

    $this->assertCollectionResponse($response, ApiResponse::districtFields());
    $this->assertPaginatedTotal($response, $expected);
})->with('possibleSearchDistricts');

it('lists villages in a regency', function (?string $search) {
    /** @var TestCase $this */
    $expected = Village::where('regency_code', '33.75')
        ->when($search, fn ($query) => $query->search($search))
        ->count();

    $response = getJson($this->path('33.75/villages', ['search' => $search]));

    $this->assertCollectionResponse($response, ApiResponse::villageFields());
    $this->assertPaginatedTotal($response, $expected);
})->with('possibleSearchVillages');
