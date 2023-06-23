<?php

declare(strict_types=1);

namespace Creasi\Tests;

use Creasi\Nusa\ServiceProvider;
use Creasi\Scripts\DatabaseSeeder;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(\dirname(__DIR__).'/database/migrations');

        $this->seed(DatabaseSeeder::class);
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        if (! file_exists(__DIR__.'/nusa.sqlite')) {
            @\touch(__DIR__.'/nusa.sqlite');
        }

        $app->useEnvironmentPath(\dirname(__DIR__));

        tap($app->make('config'), function (Repository $config) {
            $config->set('database.default', $config->get('creasi.nusa.connection'));
            // $config->set('database.connections.nusa.database', __DIR__.'/nusa.sqlite');
        });
    }
}
