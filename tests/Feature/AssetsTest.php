<?php

declare(strict_types=1);

use Creasi\Tests\Feature\TestCase;

beforeEach(function () {
    /** @var TestCase $this */
    $this->path = '';
});

afterEach(function () {
    @unlink(public_path('vendor/nusa/nusa.js'));
});

it('inlines the shipped script when the asset is not published', function () {
    /** @var TestCase $this */
    $html = (string) $this->blade('@nusaAssets');

    expect($html)
        ->toContain('<script>')
        ->toContain('alpine:init')
        ->not->toContain('defer');
});

it('loads the published script without defer', function () {
    /** @var TestCase $this */
    $published = public_path('vendor/nusa/nusa.js');

    if (! is_dir($dir = \dirname($published))) {
        mkdir($dir, 0777, true);
    }

    file_put_contents($published, '// published');

    $html = (string) $this->blade('@nusaAssets');

    // `defer` would let a deferred Alpine dispatch `alpine:init` before this script
    // runs, leaving every component unregistered.
    expect($html)
        ->toContain('<script src=')
        ->toContain('vendor/nusa/nusa.js')
        ->not->toContain('defer');
});
