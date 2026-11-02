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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Create an isolated database directory for a provider test.
 */
function temporaryDatabaseDirectory(): string
{
    $directory = sys_get_temp_dir().'/nusa-provider-'.Str::lower(Str::random(8));

    mkdir($directory, 0777, true);

    return $directory;
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
    $directory = temporaryDatabaseDirectory();
    $branch = 'test-provider-'.Str::lower(Str::random(8));
    $full = "{$directory}/nusa.{$branch}-full.sqlite";
    $dist = "{$directory}/nusa.{$branch}.sqlite";
    $previousBranch = getenv('GIT_BRANCH');
    $previousDirectory = getenv('NUSA_DATABASE_DIR');

    try {
        set_test_env('NUSA_DATABASE_DIR', $directory);
        set_test_env('GIT_BRANCH', $branch);
        $this->refreshApplication();

        expect(file_exists($full))->toBeTrue()
            ->and(file_exists($dist))->toBeTrue();
    } finally {
        set_test_env('GIT_BRANCH', $previousBranch === false ? null : $previousBranch);
        set_test_env('NUSA_DATABASE_DIR', $previousDirectory === false ? null : $previousDirectory);
        File::deleteDirectory($directory);
        $this->refreshApplication();
    }
});

it('renames the main database files for the current branch', function () {
    /** @var TestCase $this */
    $directory = temporaryDatabaseDirectory();
    $branch = 'test-provider-'.Str::lower(Str::random(8));
    $main = "{$directory}/nusa.main-full.sqlite";
    $branchPath = "{$directory}/nusa.{$branch}-full.sqlite";
    $previousBranch = getenv('GIT_BRANCH');
    $previousDirectory = getenv('NUSA_DATABASE_DIR');

    try {
        file_put_contents($main, '');
        set_test_env('NUSA_DATABASE_DIR', $directory);
        set_test_env('GIT_BRANCH', $branch);
        $this->refreshApplication();

        expect(file_exists($branchPath))->toBeTrue()
            ->and(file_exists($main))->toBeFalse();
    } finally {
        set_test_env('GIT_BRANCH', $previousBranch === false ? null : $previousBranch);
        set_test_env('NUSA_DATABASE_DIR', $previousDirectory === false ? null : $previousDirectory);
        File::deleteDirectory($directory);
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
