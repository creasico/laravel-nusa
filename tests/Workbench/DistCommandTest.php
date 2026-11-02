<?php

declare(strict_types=1);

use Creasi\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

it('creates a distribution database and restores the development database', function () {
    /** @var TestCase $this */
    $root = \dirname(__DIR__, 2);
    $branch = 'test-dist-'.Str::lower(Str::random(8));
    $devPath = "{$root}/database/nusa.{$branch}-full.sqlite";
    $distPath = "{$root}/database/nusa.{$branch}.sqlite";
    $backupPath = $devPath.'.backup';
    $previousBranch = getenv('GIT_BRANCH');
    $previousDatabase = config('database.connections.nusa.database');

    try {
        copy("{$root}/database/nusa.sqlite", $devPath);

        config(['database.connections.nusa.database' => $devPath]);
        DB::purge('nusa');
        DB::connection('nusa')->table('provinces')->where('code', '33')->update([
            'coordinates' => '[[[110.0, -6.9], [110.1, -6.9]]]',
        ]);

        set_test_env('GIT_BRANCH', $branch);

        $this->artisan('nusa:dist', ['--force' => true])->assertExitCode(0);

        expect(file_exists($distPath))->toBeTrue()
            ->and(file_exists($backupPath))->toBeFalse()
            ->and(DB::connection('nusa')->table('provinces')->where('code', '33')->value('coordinates'))->not->toBeNull();

        DB::purge('dist');

        expect(DB::connection('dist')->table('provinces')->where('code', '33')->value('coordinates'))->toBeNull();
    } finally {
        set_test_env('GIT_BRANCH', $previousBranch === false ? null : $previousBranch);
        config(['database.connections.nusa.database' => $previousDatabase]);
        DB::purge('nusa');
        DB::purge('dist');
        @unlink($devPath);
        @unlink($distPath);
        @unlink($backupPath);
    }
});

it('uses the configured database directory', function () {
    /** @var TestCase $this */
    $root = \dirname(__DIR__, 2);
    $directory = sys_get_temp_dir().'/nusa-dist-path-'.Str::lower(Str::random(8));
    $branch = 'test-dist-path-'.Str::lower(Str::random(8));
    $previousDirectory = getenv('NUSA_DATABASE_DIR');
    $previousBranch = getenv('GIT_BRANCH');

    mkdir($directory, 0777, true);

    try {
        set_test_env('NUSA_DATABASE_DIR', $directory);
        set_test_env('GIT_BRANCH', $branch);
        $this->refreshApplication();

        copy("{$root}/database/nusa.sqlite", "{$directory}/nusa.{$branch}-full.sqlite");

        $this->artisan('nusa:dist', ['--force' => true])->assertExitCode(0);

        expect(file_exists("{$directory}/nusa.{$branch}.sqlite"))->toBeTrue()
            ->and(file_exists("{$root}/database/nusa.{$branch}.sqlite"))->toBeFalse();
    } finally {
        set_test_env('NUSA_DATABASE_DIR', $previousDirectory === false ? null : $previousDirectory);
        set_test_env('GIT_BRANCH', $previousBranch === false ? null : $previousBranch);
        File::deleteDirectory($directory);
        $this->refreshApplication();
    }
});

it('fails when the backup cannot be created', function () {
    $root = \dirname(__DIR__, 2);
    $branch = 'test-dist-backup-'.Str::lower(Str::random(8));
    $devPath = "{$root}/database/nusa.{$branch}-full.sqlite";
    $previous = getenv('GIT_BRANCH');

    try {
        copy("{$root}/database/nusa.sqlite", $devPath);
        set_test_env('GIT_BRANCH', $branch);
        File::shouldReceive('copy')->once()->andReturnFalse();

        expect(fn () => $this->artisan('nusa:dist', ['--force' => true])->run())
            ->toThrow(Exception::class, 'Failed to create backup of development database');

        expect(file_exists($devPath))->toBeTrue();
    } finally {
        set_test_env('GIT_BRANCH', $previous === false ? null : $previous);
        @unlink($devPath);
        @unlink("{$root}/database/nusa.{$branch}.sqlite");
        @unlink("{$devPath}.backup");
    }
});

it('restores the development database when copying to dist fails', function () {
    $root = \dirname(__DIR__, 2);
    $branch = 'test-dist-copy-'.Str::lower(Str::random(8));
    $devPath = "{$root}/database/nusa.{$branch}-full.sqlite";
    $distPath = "{$root}/database/nusa.{$branch}.sqlite";
    $previous = getenv('GIT_BRANCH');
    $calls = 0;

    try {
        copy("{$root}/database/nusa.sqlite", $devPath);
        $original = file_get_contents($devPath);
        set_test_env('GIT_BRANCH', $branch);

        File::shouldReceive('copy')->andReturnUsing(function (string $from, string $to) use (&$calls) {
            $calls++;

            return $calls === 2 ? false : copy($from, $to);
        });
        File::shouldReceive('delete')->andReturnUsing(fn (string $path) => unlink($path));

        expect(fn () => $this->artisan('nusa:dist', ['--force' => true])->run())
            ->toThrow(Exception::class, 'Failed to copy database to distribution path');

        expect(file_get_contents($devPath))->toBe($original)
            ->and(file_exists("{$devPath}.backup"))->toBeFalse();
    } finally {
        set_test_env('GIT_BRANCH', $previous === false ? null : $previous);
        @unlink($devPath);
        @unlink($distPath);
        @unlink("{$devPath}.backup");
    }
});

it('keeps the backup when restoring the development database fails', function () {
    $root = \dirname(__DIR__, 2);
    $branch = 'test-dist-restore-'.Str::lower(Str::random(8));
    $devPath = "{$root}/database/nusa.{$branch}-full.sqlite";
    $distPath = "{$root}/database/nusa.{$branch}.sqlite";
    $previous = getenv('GIT_BRANCH');
    $calls = 0;

    try {
        copy("{$root}/database/nusa.sqlite", $devPath);
        set_test_env('GIT_BRANCH', $branch);

        File::shouldReceive('copy')->andReturnUsing(function (string $from, string $to) use (&$calls) {
            $calls++;

            return $calls === 3 ? false : copy($from, $to);
        });

        $this->artisan('nusa:dist', ['--force' => true])
            ->expectsOutputToContain('Failed to restore development database')
            ->assertExitCode(0);

        expect(file_exists("{$devPath}.backup"))->toBeTrue();
    } finally {
        set_test_env('GIT_BRANCH', $previous === false ? null : $previous);
        @unlink($devPath);
        @unlink($distPath);
        @unlink("{$devPath}.backup");
    }
});

it('fails when the development database is missing', function () {
    $root = \dirname(__DIR__, 2);
    $branch = 'test-dist-missing-'.Str::lower(Str::random(8));
    $previous = getenv('GIT_BRANCH');

    try {
        set_test_env('GIT_BRANCH', $branch);

        $this->artisan('nusa:dist')
            ->expectsOutputToContain('Development database not found')
            ->assertExitCode(1);
    } finally {
        set_test_env('GIT_BRANCH', $previous === false ? null : $previous);
        @unlink("{$root}/database/nusa.{$branch}-full.sqlite");
        @unlink("{$root}/database/nusa.{$branch}.sqlite");
    }
});

it('cancels overwriting an existing distribution database', function () {
    $root = \dirname(__DIR__, 2);
    $branch = 'test-dist-cancel-'.Str::lower(Str::random(8));
    $devPath = "{$root}/database/nusa.{$branch}-full.sqlite";
    $distPath = "{$root}/database/nusa.{$branch}.sqlite";
    $previous = getenv('GIT_BRANCH');

    try {
        copy("{$root}/database/nusa.sqlite", $devPath);
        touch($distPath);
        set_test_env('GIT_BRANCH', $branch);

        $this->artisan('nusa:dist')
            ->expectsConfirmation('Distribution database already exists. Overwrite?', 'no')
            ->expectsOutputToContain('Operation cancelled.')
            ->assertExitCode(0);

        expect(file_exists($distPath))->toBeTrue();
    } finally {
        set_test_env('GIT_BRANCH', $previous === false ? null : $previous);
        @unlink($devPath);
        @unlink($distPath);
    }
});
