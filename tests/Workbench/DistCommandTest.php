<?php

declare(strict_types=1);

use Creasi\Tests\TestCase;
use Illuminate\Support\Facades\DB;
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
