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

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        tap($app->make('config'), function (Repository $config) {
            $config->set('app.locale', 'id');
            $config->set('app.faker_locale', 'id_ID');

            // $conn = env('DB_CONNECTION', 'sqlite');

            // $conn = $config->get('database.default');

            // if ($conn === 'sqlite') {
            //     // $database = __DIR__.'/test.sqlite';

            //     // if (self::$shouldMigrate) {
            //     //     $this->recreateDatabase($database);
            //     // }

            //     $this->mergeConfig($config, 'database.connections.sqlite', [
            //         'database' => ':memory:',
            //         'foreign_key_constraints' => true,
            //     ]);
            // } else {
            //     $this->mergeConfig($config, 'database.connections.'.$conn, [
            //         'database' => env('DB_DATABASE', 'creasi_test'),
            //         'username' => env('DB_USERNAME', 'creasico'),
            //         'password' => env('DB_PASSWORD', 'secret'),
            //     ]);
            // }
        });
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
