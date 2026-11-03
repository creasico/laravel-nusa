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
    $html = (string) $this->blade('<x-nusa::postal id="nusa-postal" />');

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

it('associates its message only while it is shown', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::postal id="nusa-postal" />');

    expect($html)
        ->toContain('id="nusa-postal-message"')
        ->toContain('role="status"')
        ->toContain("x-bind:aria-describedby=\"error ? 'nusa-postal-message' : null\"")
        ->toContain('x-bind:hidden="! error"');
});

it('carries the message shown for an unknown code', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::postal id="nusa-postal" />');

    expect($html)->toContain('Postal code not found.');
});

it('carries a separate message for a failed lookup', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::postal id="nusa-postal" />');

    expect($html)->toContain('data-failed="Could not look up the postal code."');
});

it('accepts the container it should fill', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::postal id="nusa-postal" for="my-regions" />');

    expect($html)->toContain('nusaPostal(')
        ->toContain('my-regions');
});

it('renders the prefilled value', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::postal id="nusa-postal" value="51252" />');

    expect($html)->toContain('value="51252"');
});

it('follows the application locale', function () {
    /** @var TestCase $this */
    app()->setLocale('id');

    $html = (string) $this->blade('<x-nusa::postal id="nusa-postal" />');

    expect($html)->toContain('Kode Pos')
        ->toContain('Kode pos tidak ditemukan.');
});

it('gives every instance a different id', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::postal /><x-nusa::postal />');

    preg_match_all('/id="(nusa-postal-[a-z0-9]{8})/', $html, $matches);

    // Two instances, each contributing an input and a message sharing one prefix.
    expect($matches[1])->toHaveCount(4)
        ->and(array_unique($matches[1]))->toHaveCount(2);
});
