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
    $this->blade('<x-nusa::select level="province" />')
        ->assertSee('id="nusa-province"', false)
        ->assertSee('name="province_code"', false)
        ->assertSee('autocomplete="address-level1"', false)
        ->assertDontSee('aria-describedby', false)
        ->assertSee('Jawa Tengah');
});

it('disables a level until its parent is chosen', function (string $level, string $message) {
    /** @var TestCase $this */
    $html = (string) $this->blade("<x-nusa::select level=\"{$level}\" />");

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
