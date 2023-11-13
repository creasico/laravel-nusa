<?php

declare(strict_types=1);

namespace Creasi\Tests;

use Creasi\Nusa\ServiceProvider;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Config\Repository;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    private static $shouldMigrate = true;

    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $nusa = \config('database.connections.nusa');

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

            $conn = env('DB_CONNECTION', 'sqlite');

            $config->set('database.default', $conn);

            if ($conn === 'sqlite') {
                $database = __DIR__.'/test.sqlite';

                if (self::$shouldMigrate) {
                    $this->recreateDatabase($database);
                }

                $this->mergeConfig($config, 'database.connections.sqlite', [
                    'database' => $database,
                    'foreign_key_constraints' => true,
                ]);
            } else {
                $this->mergeConfig($config, 'database.connections.'.$conn, [
                    'database' => env('DB_DATABASE', 'creasi_test'),
                    'username' => env('DB_USERNAME', 'creasico'),
                    'password' => env('DB_PASSWORD', 'secret'),
                ]);
            }
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
