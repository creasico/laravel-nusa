<?php

declare(strict_types=1);

use Creasi\Tests\Feature\TestCase as FeatureTestCase;
use Creasi\Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

pest()->extend(TestCase::class)->in('Unit', 'Workbench');
pest()->extend(FeatureTestCase::class)->in('Feature');

/**
 * Flush the cache using an isolated array store.
 */
function test_flush_cache(): void
{
    config(['cache.default' => 'array']);
    Cache::flush();
}

/**
 * Determine whether a database connection is reachable.
 */
function test_can_connect(string $connection): bool
{
    try {
        DB::connection($connection)->getPdo();

        return true;
    } catch (Throwable $e) {
        $message = strtolower($e->getMessage());
        $unavailable = str_contains($message, 'could not find driver')
            || str_contains($message, 'connection refused')
            || str_contains($message, 'no such file or directory')
            || str_contains($message, 'timed out')
            || str_contains($message, 'server not found');

        if ($unavailable) {
            return false;
        }

        throw $e;
    }
}

/**
 * Set an environment variable for the current test process.
 */
function set_test_env(string $key, ?string $value): void
{
    if ($value === null) {
        putenv($key);
        unset($_ENV[$key], $_SERVER[$key]);

        return;
    }

    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}
