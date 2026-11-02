<?php

declare(strict_types=1);

use Creasi\Tests\Fixture\FakeImportCommand;
use Creasi\Tests\TestCase;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Workbench\App\Console\DistCommand;
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
    $submodules = \dirname(__DIR__, 2).'/workbench/submodules';
    $required = [
        'cahyadsn-wilayah/db/wilayah.sql',
        'cahyadsn-wilayah_kodepos/db/wilayah_kodepos.sql',
        'cahyadsn-wilayah_boundaries/db/ddl_wilayah_boundaries.sql',
    ];

    foreach ($required as $file) {
        if (! file_exists("{$submodules}/{$file}")) {
            $this->markTestSkipped('Upstream submodules are not checked out.');
        }
    }

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

it('imports scanned sql and seeds fetched rows', function () {
    /** @var TestCase $this */
    $root = \dirname(__DIR__, 2);
    $database = "{$root}/workbench/storage/import-".Str::lower(Str::random(8)).'.sqlite';
    $previousNusa = config('database.connections.nusa.database');
    $previousUpstream = config('database.connections.upstream');

    try {
        touch($database);
        config([
            'database.connections.nusa.database' => $database,
            'database.connections.upstream' => [
                'driver' => 'sqlite',
                'database' => $database,
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('nusa');
        DB::purge('upstream');

        $this->artisan('migrate:fresh', [
            '--database' => 'nusa',
            '--path' => "{$root}/database/migrations",
            '--realpath' => true,
        ])->assertExitCode(0);

        DB::connection('upstream')->statement('CREATE TABLE wilayah (kode varchar(13), nama varchar(50))');
        DB::connection('upstream')->statement('CREATE TABLE wilayah_kodepos (kode varchar(13), kodepos varchar(5))');
        DB::connection('upstream')->statement('CREATE TABLE wilayah_boundaries (kode varchar(13), lat double, lng double, path text)');

        $command = new FakeImportCommand;
        $command->setLaravel($this->app);
        $command->scanned = [
            'fixture.sql' => "INSERT INTO wilayah (kode, nama) VALUES ('11', 'Aceh');",
        ];
        $command->fetched = [
            'provinces' => [[
                'code' => '11',
                'name' => 'Aceh',
                'latitude' => 5.5,
                'longitude' => 95.3,
                'coordinates' => null,
            ]],
        ];

        $exit = $command->run(new ArrayInput([]), new BufferedOutput);

        expect($exit)->toBe(0)
            ->and(DB::connection('nusa')->table('provinces')->where('code', '11')->exists())->toBeTrue()
            ->and(DB::connection('upstream')->table('wilayah')->count())->toBe(1);
    } finally {
        config([
            'database.connections.nusa.database' => $previousNusa,
            'database.connections.upstream' => $previousUpstream,
        ]);
        DB::purge('nusa');
        DB::purge('upstream');
        @unlink($database);
    }
});

it('runs fresh and dist flows', function () {
    $previousUpstream = config('database.connections.upstream');

    try {
        config(['database.connections.upstream' => ['driver' => 'sqlite', 'database' => ':memory:']]);
        DB::purge('upstream');
        DB::connection('upstream')->statement('CREATE TABLE wilayah (kode varchar(13), nama varchar(50))');
        DB::connection('upstream')->statement('CREATE TABLE wilayah_kodepos (kode varchar(13), kodepos varchar(5))');
        DB::connection('upstream')->statement('CREATE TABLE wilayah_boundaries (kode varchar(13), lat double, lng double, path text)');

        $command = new FakeImportCommand;
        $command->setLaravel($this->app);
        $command->scanned = [];
        $command->fetched = [];

        $exit = $command->run(new ArrayInput(['--fresh' => true, '--dist' => true]), new BufferedOutput);

        expect($exit)->toBe(0)
            ->and($command->refreshed)->toBeTrue()
            ->and($command->calls)->toContain(DistCommand::class);
    } finally {
        config(['database.connections.upstream' => $previousUpstream]);
        DB::purge('upstream');
    }
});

it('imports safe statements and skips unsafe ones', function () {
    $previous = DB::getDefaultConnection();
    config(['database.connections.import_test' => ['driver' => 'sqlite', 'database' => ':memory:']]);
    DB::purge('import_test');
    DB::connection('import_test')->statement('CREATE TABLE items (id integer)');
    DB::setDefaultConnection('import_test');

    try {
        $method = new ReflectionMethod(ImportCommand::class, 'importQuery');

        $method->invoke(
            new ImportCommand,
            'INSERT INTO items VALUES (1); DELETE FROM items; INSERT INTO items VALUES (2);'
        );

        expect(DB::connection('import_test')->table('items')->count())->toBe(2);
    } finally {
        DB::setDefaultConnection($previous);
        DB::purge('import_test');
    }
});

it('flushes pending inserts before executing ddl', function () {
    $previous = DB::getDefaultConnection();
    config(['database.connections.import_test' => ['driver' => 'sqlite', 'database' => ':memory:']]);
    DB::purge('import_test');
    DB::connection('import_test')->statement('CREATE TABLE items (id integer)');
    DB::setDefaultConnection('import_test');

    try {
        $method = new ReflectionMethod(ImportCommand::class, 'importQuery');

        $method->invoke(
            new ImportCommand,
            'INSERT INTO items VALUES (1); CREATE TABLE snapshot AS SELECT * FROM items; INSERT INTO items VALUES (2);'
        );

        expect(DB::connection('import_test')->table('items')->count())->toBe(2)
            ->and(DB::connection('import_test')->table('snapshot')->count())->toBe(1);
    } finally {
        DB::setDefaultConnection($previous);
        DB::purge('import_test');
    }
});

it('recreates and migrates the database', function () {
    $root = \dirname(__DIR__, 2);
    $database = "{$root}/workbench/storage/import-".Str::lower(Str::random(8)).'.sqlite';
    $previous = config('database.connections.nusa.database');

    try {
        touch($database);
        config(['database.connections.nusa.database' => $database]);
        DB::purge('nusa');

        $kernel = $this->app[Kernel::class];
        $kernel->all();
        $artisan = (new ReflectionProperty($kernel, 'artisan'))->getValue($kernel);

        $input = new ArrayInput([]);

        $command = new ImportCommand;
        $command->setLaravel($this->app);
        $command->setApplication($artisan);
        $command->setOutput(new OutputStyle($input, new BufferedOutput));
        (new ReflectionProperty($command, 'input'))->setValue($command, $input);

        (new ReflectionMethod($command, 'refreshDatabase'))->invoke($command);

        expect(file_exists($database))->toBeTrue()
            ->and(Schema::connection('nusa')->hasTable('provinces'))->toBeTrue();
    } finally {
        config(['database.connections.nusa.database' => $previous]);
        DB::purge('nusa');
        @unlink($database);
    }
});

it('normalizes fetched upstream rows', function () {
    $connection = Mockery::mock();
    $connection->shouldReceive('select')->once()->andReturn([
        (object) ['kode' => '11', 'nama' => 'aceh', 'kodepos' => '12345', 'lat' => 5.5, 'lng' => 95.3, 'path' => null],
        (object) ['kode' => '123', 'nama' => 'invalid', 'kodepos' => null, 'lat' => null, 'lng' => null, 'path' => null],
    ]);

    DB::shouldReceive('connection')->with('upstream')->andReturn($connection);

    $command = new ImportCommand;
    $command->setOutput(new OutputStyle(new ArrayInput([]), new BufferedOutput));

    $result = (new ReflectionMethod($command, 'fetchAll'))->invoke($command);

    expect($result['provinces'])->toHaveCount(1)
        ->and($result['provinces'][0]['name'])->toBe('Aceh');
});
