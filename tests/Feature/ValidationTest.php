<?php

declare(strict_types=1);

use Creasi\Tests\Feature\TestCase;

use function Pest\Laravel\getJson;

beforeEach(function () {
    /** @var TestCase $this */
    $this->path = '';
});

it('rejects invalid query parameters', function (array $query) {
    /** @var TestCase $this */
    getJson('nusa/provinces?'.http_build_query($query))->assertUnprocessable();
})->with([
    'page is not numeric' => [['page' => 'abc']],
    'per-page is not numeric' => [['per-page' => 'abc']],
    'with is not an array' => [['with' => 'regencies']],
    'codes is not an array' => [['codes' => '33']],
    'search is not a string' => [['search' => ['jawa']]],
]);

it('rejects invalid postal codes', function (string $postalCode) {
    /** @var TestCase $this */
    getJson('nusa/villages?'.http_build_query(['postal_code' => $postalCode]))
        ->assertUnprocessable();
})->with([
    'non-numeric' => ['abc'],
    'too short' => ['123'],
    'too long' => ['123456'],
]);

it('honors the per-page parameter', function () {
    getJson('nusa/provinces?per-page=5')
        ->assertOk()
        ->assertJsonPath('meta.per_page', 5)
        ->assertJsonCount(5, 'data');
});

it('returns a different second page', function () {
    $first = getJson('nusa/provinces?per-page=5&page=1')->json('data.0.code');
    $second = getJson('nusa/provinces?per-page=5&page=2')->json('data.0.code');

    expect($second)->not->toBe($first);
});

it('returns an empty page beyond the last page', function () {
    getJson('nusa/provinces?page=999')
        ->assertOk()
        ->assertJsonPath('meta.current_page', 999)
        ->assertJsonCount(0, 'data');
});

it('returns an empty result for a missing search term', function () {
    getJson('nusa/provinces?search=zzzzzz')
        ->assertOk()
        ->assertJsonPath('meta.total', 0)
        ->assertJsonCount(0, 'data');
});

it('ignores unknown relations on a list', function () {
    getJson('nusa/provinces?with[]=does_not_exist')
        ->assertOk()
        ->assertJsonMissingPath('data.0.does_not_exist');
});
