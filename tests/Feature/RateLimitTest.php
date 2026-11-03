<?php

declare(strict_types=1);

use Creasi\Tests\Feature\TestCase;

use function Pest\Laravel\getJson;

beforeEach(function () {
    /** @var TestCase $this */
    $this->path = '';
});

it('throttles requests beyond the configured limit', function () {
    /** @var TestCase $this */
    config(['creasi.nusa.rate_limit' => 2]);

    getJson('nusa/provinces')->assertOk();
    getJson('nusa/provinces')->assertOk();

    getJson('nusa/provinces')
        ->assertStatus(429)
        ->assertHeader('Retry-After')
        ->assertJsonPath('message', 'Too Many Attempts.');
});

it('counts both route families against the same limit', function () {
    /** @var TestCase $this */
    config(['creasi.nusa.rate_limit' => 1]);

    getJson('nusa/provinces')->assertOk();
    getJson('nusa/33')->assertStatus(429);
});

it('can be disabled', function () {
    /** @var TestCase $this */
    config(['creasi.nusa.rate_limit' => 0]);

    foreach (range(1, 5) as $attempt) {
        getJson('nusa/provinces')->assertOk();
    }
});
