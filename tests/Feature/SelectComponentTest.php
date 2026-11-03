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

it('renders every province', function () {
    /** @var TestCase $this */
    $this->blade('<x-nusa::select level="province" id="nusa-province" />')
        ->assertSee('id="nusa-province"', false)
        ->assertSee('name="province_code"', false)
        ->assertSee('autocomplete="address-level1"', false)
        ->assertDontSee('aria-describedby', false)
        ->assertSee('Jawa Tengah');
});

it('disables a level until its parent is chosen', function (string $level, string $message) {
    /** @var TestCase $this */
    $html = (string) $this->blade("<x-nusa::select level=\"{$level}\" id=\"nusa-{$level}\" />");

    expect($html)->toMatch('/<select[^>]*\sdisabled[\s>]/')
        ->toContain("aria-describedby=\"nusa-{$level}-message\"")
        ->toContain("id=\"nusa-{$level}-message\"")
        ->toContain('role="status"')
        ->toContain($message);
})->with([
    'regency' => ['regency', 'Choose Province first'],
    'district' => ['district', 'Choose Regency first'],
    'village' => ['village', 'Choose District first'],
]);

it('lists the children of an explicit parent', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::select level="regency" parent="33" />');

    expect($html)->toContain('Kota Semarang')
        ->not->toContain('Kabupaten Bandung')
        ->not->toMatch('/<select[^>]*\sdisabled[\s>]/');
});

it('derives the parent from the selected code', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::select level="district" value="33.75.03" />');

    expect($html)->not->toMatch('/<select[^>]*\sdisabled[\s>]/')
        ->toContain('value="33.75.03"')
        ->toContain('selected')
        ->toContain('Pekalongan Utara');
});

it('pairs every label with its control', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::select level="province" />');

    preg_match('/<label for="([^"]+)"/', $html, $label);
    preg_match('/<select[^>]*id="([^"]+)"/', $html, $select);

    expect($label[1] ?? null)->toBe($select[1] ?? null)->not->toBeNull();
});

it('follows the application locale', function () {
    /** @var TestCase $this */
    app()->setLocale('id');

    $html = (string) $this->blade('<x-nusa::select level="regency" />');

    expect($html)->toContain('Kota/Kabupaten')
        ->toContain('Pilih Provinsi terlebih dahulu');
});

it('gives every instance a different id', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::select level="province" /><x-nusa::select level="province" />');

    preg_match_all('/id="(nusa-province-[^"]+)"/', $html, $matches);

    expect($matches[1])->toHaveCount(2)
        ->and(array_unique($matches[1]))->toHaveCount(2);
});

it('keeps a caller-supplied description', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::select level="regency" id="my-regency" aria-describedby="help-text" />');

    // The Alpine binding (`x-bind:aria-describedby`) is not the attribute itself.
    preg_match_all('/\saria-describedby="/', $html, $attributes);

    expect($html)->toContain('aria-describedby="help-text my-regency-message"')
        ->and($attributes[0])->toHaveCount(1);
});

it('exposes the empty and failed messages', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('<x-nusa::select level="regency" id="my-regency" />');

    expect($html)
        ->toContain('data-empty="No options available."')
        ->toContain('data-failed="Could not load the options."');
});
