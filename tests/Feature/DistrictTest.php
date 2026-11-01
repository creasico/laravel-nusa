<?php

declare(strict_types=1);

use Creasi\Nusa\Models\District;
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
    $this->path = 'nusa/districts';
});

dataset('availableQueries', fn () => [
    'basic request' => [],
    'include province' => ['province'],
    'include regency' => ['regency'],
    'include villages' => ['villages'],
]);

dataset('possibleSearchVillages', fn () => [
    'no search' => [null],
    'with keyword' => ['dukuh'],
]);

it('lists districts', function (?string ...$with) {
    /** @var TestCase $this */
    $response = getJson($this->path(query: ['with' => $with, 'per-page' => 5]));

    $this->assertCollectionResponse($response, ApiResponse::districtFields(), $with);
})->with('availableQueries');

it('rejects invalid codes', function (mixed $codes) {
    /** @var TestCase $this */
    $response = getJson($this->path(query: ['codes' => $codes]));

    $response->assertUnprocessable();
})->with(['non-array of numeric code' => ['33.75.03']]);

it('filters districts by selected codes', function () {
    /** @var TestCase $this */
    $response = getJson($this->path(query: ['codes' => ['33.75.03', '33.75.04']]));

    $this->assertCollectionResponse($response, ApiResponse::districtFields())
        ->assertJsonCount(2, 'data');
});

it('searches districts by name', function () {
    /** @var TestCase $this */
    $expected = District::search('Pekalongan')->count();

    $response = getJson($this->path(query: ['search' => 'Pekalongan']));

    $this->assertCollectionResponse($response, ApiResponse::districtFields());
    $this->assertPaginatedTotal($response, $expected);
});

it('shows a single district', function (?string ...$with) {
    /** @var TestCase $this */
    $response = getJson($this->path('33.75.03', ['with' => $with]));

    $this->assertSingleResponse($response, ApiResponse::districtFields(), $with)
        ->assertJsonPath('data.code', '33.75.03');
})->with('availableQueries');

it('returns 404 for an unknown district', function () {
    /** @var TestCase $this */
    getJson($this->path('99.99.99'))->assertNotFound();
});

it('lists villages in a district', function (?string $search) {
    /** @var TestCase $this */
    $expected = Village::where('district_code', '33.75.03')
        ->when($search, fn ($query) => $query->search($search))
        ->count();

    $response = getJson($this->path('33.75.03/villages', ['search' => $search]));

    $this->assertCollectionResponse($response, ApiResponse::villageFields());
    $this->assertPaginatedTotal($response, $expected);
})->with('possibleSearchVillages');

it('includes relations in a nested list', function () {
    /** @var TestCase $this */
    $response = getJson($this->path('33.75.03/villages', ['with' => ['regency'], 'per-page' => 2]));

    $this->assertCollectionResponse($response, ApiResponse::villageFields(), ['regency']);
});
