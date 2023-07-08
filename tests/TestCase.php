<?php

declare(strict_types=1);

namespace Creasi\Tests;

use Creasi\Nusa\ServiceProvider;
use Illuminate\Config\Repository;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    // use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app->useEnvironmentPath(\dirname(__DIR__));

        tap($app->make('config'), function (Repository $config) {
            $config->set('app.locale', 'id');
            $config->set('app.faker_locale', 'id_ID');

            if (! env('DB_CONNECTION')) {
                $config->set('database.default', 'testing');

                $database = __DIR__.'/nusa.sqlite';

                if (! file_exists($database)) {
                    touch($database);
                }

                $config->set('database.connections.testing', [
                    'driver' => 'sqlite',
                    'database' => $database,
                    'foreign_key_constraints' => true,
                ]);
            }
        });
    }
}
