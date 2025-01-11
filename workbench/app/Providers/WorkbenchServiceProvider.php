<?php

namespace Workbench\App\Providers;

use Illuminate\Config\Repository;
use Illuminate\Support\ServiceProvider;
use Workbench\App\Console\DatabaseImport;
use Workbench\App\Console\StatCommand;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (app()->runningInConsole()) {
            $this->commands([
                StatCommand::class,
                DatabaseImport::class,
            ]);
        }

        tap(app()->make('config'), function (Repository $config) {
            $config->set('app.locale', 'id');
            $config->set('app.faker_locale', 'id_ID');

            if (app()->runningInConsole()) {
                $nusa = $config->get('database.connections.nusa', []);

                $this->loadMigrationsFrom(\dirname($nusa['database']).'/migrations');
            }

            $config->set([
                'database.connections.upstream' => [
                    'driver' => 'mysql',
                    'host' => env('UPSTREAM_DB_HOST', env('DB_HOST', '127.0.0.1')),
                    'port' => env('DB_PORT', '3306'),
                    'database' => env('UPSTREAM_DB_DATABASE', 'nusantara'),
                    'username' => env('DB_USERNAME', 'creasico'),
                    'password' => env('DB_PASSWORD', 'secret'),
                ],
            ]);

            if (env('DB_CONNECTION') === 'sqlite') {
                if (! file_exists($database = database_path('database.sqlite'))) {
                    touch($database);
                }

                $this->mergeConfig($config, 'database.connections.testing', [
                    'database' => $database,
                    'foreign_key_constraints' => true,
                ]);
            }
        });
    }

    private function mergeConfig(Repository $config, string $key, array $value)
    {
        $config->set($key, array_merge($config->get($key, []), $value));
    }
}
