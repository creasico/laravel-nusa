<?php

declare(strict_types=1);

use Creasi\Tests\Feature\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->path = '';
});

it('emits a same-origin script tag', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('@nusaAssets');

    expect($html)
        ->toContain('<script src=')
        ->toContain('vendor/nusa/nusa.js')
        ->not->toContain('defer');
});

it('never inlines the script', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('@nusaAssets');

    // An inline copy needs `unsafe-inline` or a nonce, and a blocking policy fails
    // silently, so the published file is the only form.
    expect($html)
        ->not->toContain('alpine:init')
        ->not->toContain('nusaSelect');
});
