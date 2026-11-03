<?php

declare(strict_types=1);

use Creasi\Tests\Feature\TestCase;

beforeEach(function () {
    /**
     * @var TestCase $this
     *
     * @disregard P1056
     */
    $this->path = '';

    app()->setLocale('en');
});

it('renders a prefilled address without any request', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade(
        "<x-nusa::address-form id=\"nusa-address\" :address=\"['line' => 'Jl. Merdeka 1', 'province_code' => '33', 'regency_code' => '33.75', 'postal_code' => '51111']\" />"
    );

    expect($html)
        ->toContain('id="nusa-address-province"')
        ->toContain('value="33" selected')
        ->toContain('value="33.75" selected')
        ->toContain('name="line"')
        ->toContain('Jl. Merdeka 1');
});

it('renders every control with a paired label and a unique id', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::address-form />');

    preg_match_all('/<label for="([^"]+)"/', $html, $labels);
    preg_match_all('/<(?:input|select)[^>]*\sid="([^"]+)"/', $html, $controls);

    expect($labels[1])->toHaveCount(6)
        ->and($labels[1])->toBe($controls[1])
        ->and(array_unique($labels[1]))->toHaveCount(6);
});

it('marks up the postal input for numeric entry and autofill', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::address-form />');

    expect($html)
        ->toContain('inputmode="numeric"')
        ->toContain('maxlength="5"')
        ->toContain('autocomplete="postal-code"')
        ->toContain('role="status"');
});

it('maps every level to its autocomplete token', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::address-form />');

    expect($html)
        ->toContain('autocomplete="address-line1"')
        ->toContain('autocomplete="address-level1"')
        ->toContain('autocomplete="address-level2"')
        ->toContain('autocomplete="address-level3"')
        ->toContain('autocomplete="address-level4"');
});

it('disables the levels below the prefilled chain', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade(
        "<x-nusa::address-form id=\"nusa-address\" :address=\"['province_code' => '33']\" />"
    );

    expect($html)
        ->toContain('id="nusa-address-district-message"')
        ->toContain('Choose Regency first');
});

it('remaps the address columns', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade(
        "<x-nusa::address-form :fields=\"['line' => 'street_address', 'village_code' => 'desa_code']\" />"
    );

    expect($html)
        ->toContain('name="street_address"')
        ->toContain('name="desa_code"');
});

it('seeds alpine with the route prefix', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::address-form />');

    expect($html)
        ->toContain('x-data="nusaPostal(')
        ->toContain('/nusa');
});

it('wires the levels together through parent-id', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::address-form id="nusa-address" />');

    expect($html)
        ->toContain('data-parent-id="nusa-address-province"')
        ->toContain('data-parent-id="nusa-address-regency"')
        ->toContain('data-parent-id="nusa-address-district"')
        ->not->toContain('data-parent-id="nusa-address-village"');
});

it('leaves asset loading to the layout', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::address-form />');

    expect($html)->not->toContain('nusaRegistered');
});

it('compiles the nusaAssets directive', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('@nusaAssets');

    expect($html)->toContain('<script')
        ->toContain('alpine:init')
        ->toContain('nusaSelect');
});

it('gives every form a different id', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::address-form /><x-nusa::address-form />');

    preg_match_all('/<div id="(nusa-address-[a-z0-9]{8})"/', $html, $matches);

    expect($matches[1])->toHaveCount(2)
        ->and(array_unique($matches[1]))->toHaveCount(2);
});
