<?php

declare(strict_types=1);

use Creasi\Tests\Feature\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->path = '';

    app()->setLocale('en');
});

it('renders a standalone postal field', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::postal />');

    expect($html)
        ->toContain('id="nusa-postal"')
        ->toContain('for="nusa-postal"')
        ->toContain('name="postal_code"')
        ->toContain('inputmode="numeric"')
        ->toContain('maxlength="5"')
        ->toContain('autocomplete="postal-code"')
        ->toContain('data-nusa-postal')
        ->toContain('role="status"')
        ->toContain('Postal Code');
});

it('carries the message shown for an unknown code', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::postal />');

    expect($html)->toContain('Postal code not found.');
});

it('accepts the container it should fill', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::postal for="my-regions" />');

    expect($html)->toContain('nusaPostal(')
        ->toContain('my-regions');
});

it('renders the prefilled value', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::postal value="51252" />');

    expect($html)->toContain('value="51252"');
});

it('follows the application locale', function () {
    /** @var TestCase $this */
    app()->setLocale('id');

    $html = (string) $this->blade('<x-nusa::postal />');

    expect($html)->toContain('Kode Pos')
        ->toContain('Kode pos tidak ditemukan.');
});
