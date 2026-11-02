<?php

declare(strict_types=1);

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
    $this->path = 'nusa/villages';
});

dataset('availableQueries', fn () => [
    'basic request' => [],
    'include province' => ['province'],
    'include regency' => ['regency'],
    'include district' => ['district'],
]);

it('lists villages', function (?string ...$with) {
    /** @var TestCase $this */
    $response = getJson($this->path(query: ['with' => $with, 'per-page' => 5]));

    $this->assertCollectionResponse($response, ApiResponse::villageFields(), $with);
})->with('availableQueries');

it('rejects invalid codes', function (mixed $codes) {
    /** @var TestCase $this */
    $response = getJson($this->path(query: ['codes' => $codes]));

    $response->assertUnprocessable();
})->with(['non-array of numeric code' => ['33.75.03.1004']]);

it('filters villages by selected codes', function () {
    /** @var TestCase $this */
    $response = getJson($this->path(query: ['codes' => ['33.75.03.1004', '33.75.03.1006']]));

    $this->assertCollectionResponse($response, ApiResponse::villageFields())
        ->assertJsonCount(2, 'data');
});

it('filters villages by postal code', function () {
    /** @var TestCase $this */
    $postalCode = 51252;
    $expected = Village::where('postal_code', $postalCode)->count();

    $response = getJson($this->path(query: ['postal_code' => $postalCode]));

    $this->assertCollectionResponse($response, ApiResponse::villageFields());
    $this->assertPaginatedTotal($response, $expected);

    foreach ($response->json('data') as $village) {
        expect($village['postal_code'])->toBe($postalCode);
    }
});

it('searches villages by name', function () {
    /** @var TestCase $this */
    $expected = Village::search('Padukuhan Kraton')->count();

    $response = getJson($this->path(query: ['search' => 'Padukuhan Kraton']));

    $this->assertCollectionResponse($response, ApiResponse::villageFields());
    $this->assertPaginatedTotal($response, $expected);
});

it('shows a single village', function (?string ...$with) {
    /** @var TestCase $this */
    $response = getJson($this->path('33.75.03.1006', ['with' => $with]));

    $this->assertSingleResponse($response, ApiResponse::villageFields(), $with)
        ->assertJsonPath('data.code', '33.75.03.1006');
})->with('availableQueries');

it('returns 404 for an unknown village', function () {
    /** @var TestCase $this */
    getJson($this->path('99.99.99.9999'))->assertNotFound();
});
