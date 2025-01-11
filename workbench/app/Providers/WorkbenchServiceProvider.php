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
                $dbpath = $this->recreateDatabase($nusa['database']);

                $this->loadMigrationsFrom(\realpath($dbpath).'/migrations');
            }

            $config->set([
                'database.connections.upstream' => [
                    'driver' => 'mysql',
                    'host' => env('DB_HOST', '127.0.0.1'),
                    'port' => env('DB_PORT', '3306'),
                    'database' => env('DB_NUSA', 'nusantara'),
                    'username' => env('DB_USERNAME', 'creasico'),
                    'password' => env('DB_PASSWORD', 'secret'),
                ],
            ]);

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

    private function recreateDatabase(string $path): string
    {
        if (! app()->runningUnitTests()) {
            if (\file_exists($path)) {
                \unlink($path);
            }

            \touch($path);
        }

        return \dirname($path);
    }
}
