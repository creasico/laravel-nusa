<?php

declare(strict_types=1);

namespace Creasi\Tests;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use DatabaseMigrations;
    use WithWorkbench;

    private static $shouldMigrate = true;

    protected function defineDatabaseMigrations()
    {
        $nusa = \config('database.connections.nusa', []);

        if (self::$shouldMigrate) {
            $this->recreateDatabase($nusa['database']);

            $this->loadMigrationsWithoutRollbackFrom(
                \realpath(\dirname($nusa['database'])).'/migrations'
            );
        }
    }

    protected function defineDatabaseSeeders()
    {
        if (self::$shouldMigrate) {
            $this->seed(DatabaseSeeder::class);

            self::$shouldMigrate = false;
        }
    }

    private function recreateDatabase(string $path)
    {
        if (\file_exists($path)) {
            \unlink($path);
        }

        \touch($path);

        return $path;
    }

    private function mergeConfig(Repository $config, string $key, array $value)
    {
        $config->set($key, array_merge($config->get($key, []), $value));
    }
}
