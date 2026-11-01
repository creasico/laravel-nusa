<?php

declare(strict_types=1);

use Creasi\Tests\TestCase;
use Workbench\App\Console\ImportCommand;

afterEach(function () {
    ImportCommand::$cached = false;
});

it('skips importing when the cache exists', function () {
    /** @var TestCase $this */
    ImportCommand::$cached = true;

    $this->artisan('nusa:import')
        ->expectsOutputToContain('Skip importing upstream database, cache exists')
        ->assertExitCode(0);
});

it('scans the upstream sql files', function () {
    /** @var TestCase $this */
    $command = new ImportCommand;
    $method = new ReflectionMethod($command, 'scanSqlFiles');

    $files = iterator_to_array($method->invoke($command, [
        'cahyadsn-wilayah/db/wilayah.sql',
        'cahyadsn-wilayah_kodepos/db/wilayah_kodepos.sql',
    ]));

    expect($files)->toHaveKeys([
        'cahyadsn-wilayah/db/wilayah.sql',
        'cahyadsn-wilayah_kodepos/db/wilayah_kodepos.sql',
        'cahyadsn-wilayah_boundaries/db/ddl_wilayah_boundaries.sql',
    ]);

    expect(\count($files))->toBeGreaterThan(500);

    foreach ($files as $path => $sql) {
        expect($path)->toBeString()->toStartWith('cahyadsn-wilayah');
        expect($sql)->toBeString()->not->toBeEmpty();
    }
});
