<?php

declare(strict_types=1);

use Creasi\Nusa\Contracts\Address;
use Creasi\Nusa\Contracts\District;
use Creasi\Nusa\Contracts\Province;
use Creasi\Nusa\Contracts\Regency;
use Creasi\Nusa\Contracts\Village;
use Creasi\Nusa\Models\District as DistrictModel;
use Creasi\Nusa\Models\Province as ProvinceModel;
use Creasi\Nusa\Models\Regency as RegencyModel;
use Creasi\Nusa\Models\Village as VillageModel;
use Creasi\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Str;

/**
 * Move an existing database file out of the way before a test.
 */
function backupDatabaseFile(string $path): ?string
{
    if (! file_exists($path)) {
        return null;
    }

    $backup = $path.'.test-backup-'.Str::lower(Str::random(8));

    rename($path, $backup);

    return $backup;
}

/**
 * Remove a test database file and restore its previous contents, if any.
 */
function restoreDatabaseFile(string $path, ?string $backup): void
{
    if (file_exists($path)) {
        @unlink($path);
    }

    if ($backup !== null && file_exists($backup)) {
        rename($backup, $path);
    }
}

it('registers the workbench commands', function () {
    /** @var TestCase $this */
    expect(Artisan::all())->toHaveKeys([
        'nusa:import',
        'nusa:dist',
        'nusa:stat',
        'nusa:generate-static',
    ]);
});

it('configures the workbench application', function () {
    /** @var TestCase $this */
    $database = config('database.connections.nusa.database');

    expect(config('app.locale'))->toBe('id')
        ->and(config('app.faker_locale'))->toBe('id_ID')
        ->and($database)->toStartWith(\dirname(__DIR__, 2).\DIRECTORY_SEPARATOR.'database')
        ->and($database)->toEndWith('-full.sqlite')
        ->and(config('database.connections.current.database'))->toEndWith('nusa.sqlite');
});

it('uses the fork concurrency driver', function () {
    /** @var TestCase $this */
    expect(Concurrency::getDefaultInstance())->toBe('fork');
});

it('creates branch database files', function () {
    /** @var TestCase $this */
    $root = \dirname(__DIR__, 2);
    $branch = 'test-provider-'.Str::lower(Str::random(8));
    $full = "{$root}/database/nusa.{$branch}-full.sqlite";
    $dist = "{$root}/database/nusa.{$branch}.sqlite";
    $mainFull = "{$root}/database/nusa.main-full.sqlite";
    $mainDist = "{$root}/database/nusa.main.sqlite";
    $previous = getenv('GIT_BRANCH');
    $backups = [
        $full => backupDatabaseFile($full),
        $dist => backupDatabaseFile($dist),
        $mainFull => backupDatabaseFile($mainFull),
        $mainDist => backupDatabaseFile($mainDist),
    ];

    try {
        set_test_env('GIT_BRANCH', $branch);
        $this->refreshApplication();

        expect(file_exists($full))->toBeTrue()
            ->and(file_exists($dist))->toBeTrue();
    } finally {
        set_test_env('GIT_BRANCH', $previous === false ? null : $previous);

        foreach ($backups as $path => $backup) {
            restoreDatabaseFile($path, $backup);
        }

        $this->refreshApplication();
    }
});

it('renames the main database files for the current branch', function () {
    /** @var TestCase $this */
    $root = \dirname(__DIR__, 2);
    $branch = 'test-provider-'.Str::lower(Str::random(8));
    $main = "{$root}/database/nusa.main-full.sqlite";
    $mainDist = "{$root}/database/nusa.main.sqlite";
    $branchPath = "{$root}/database/nusa.{$branch}-full.sqlite";
    $dist = "{$root}/database/nusa.{$branch}.sqlite";
    $previous = getenv('GIT_BRANCH');
    $backups = [
        $main => backupDatabaseFile($main),
        $mainDist => backupDatabaseFile($mainDist),
        $branchPath => backupDatabaseFile($branchPath),
        $dist => backupDatabaseFile($dist),
    ];

    try {
        file_put_contents($main, '');
        set_test_env('GIT_BRANCH', $branch);
        $this->refreshApplication();

        expect(file_exists($branchPath))->toBeTrue()
            ->and(file_exists($main))->toBeFalse();
    } finally {
        set_test_env('GIT_BRANCH', $previous === false ? null : $previous);

        foreach ($backups as $path => $backup) {
            restoreDatabaseFile($path, $backup);
        }

        $this->refreshApplication();
    }
});

it('configures the sqlite testing connection', function () {
    /** @var TestCase $this */
    $previous = getenv('DB_CONNECTION');

    try {
        set_test_env('DB_CONNECTION', 'sqlite');
        $this->refreshApplication();

        $database = config('database.connections.testing.database');

        expect($database)->toEndWith('database.sqlite')
            ->and(file_exists($database))->toBeTrue();
    } finally {
        set_test_env('DB_CONNECTION', $previous === false ? null : $previous);
        $this->refreshApplication();
    }
});

it('binds the nusa contracts', function (string $contract, string $model) {
    /** @var TestCase $this */
    expect($this->app->make($contract))->toBeInstanceOf($model);
})->with([
    'province' => [Province::class, ProvinceModel::class],
    'regency' => [Regency::class, RegencyModel::class],
    'district' => [District::class, DistrictModel::class],
    'village' => [Village::class, VillageModel::class],
    'address' => [Address::class, Creasi\Nusa\Models\Address::class],
]);
