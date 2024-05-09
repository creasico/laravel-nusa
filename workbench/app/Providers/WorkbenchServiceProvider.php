<?php

namespace Workbench\App\Providers;

use Illuminate\Config\Repository;
use Illuminate\Support\ServiceProvider;

use function Orchestra\Testbench\workbench_path;

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
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );

        tap(app()->make('config'), function (Repository $config) {
            $config->set('app.locale', 'id');
            $config->set('app.faker_locale', 'id_ID');

            $conn = env('DB_CONNECTION', 'sqlite');

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
}
