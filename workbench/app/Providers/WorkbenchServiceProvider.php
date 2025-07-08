<?php

namespace Workbench\App\Providers;

use Illuminate\Config\Repository;
use Illuminate\Support\ServiceProvider;
use Workbench\App\Console\DistCommand;
use Workbench\App\Console\GenerateStaticCommand;
use Workbench\App\Console\ImportCommand;
use Workbench\App\Console\StatCommand;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $nusa = config('database.connections.nusa');
        $databaseDir = \dirname($nusa['database']);

        if (app()->runningInConsole()) {
            $this->loadMigrationsFrom($databaseDir.'/migrations');
        }

        $path = "{$databaseDir}/nusa.{$this->currentBranch()}.sqlite";

        if (! file_exists($path)) {
            touch($path);
        }

        config([
            'app' => [
                'locale' => 'id',
                'faker_locale' => 'id_ID',
            ],
            'database.connections.nusa' => array_merge($nusa, [
                'database' => $path,
            ]),
            'database.connections.upstream' => [
                'driver' => 'mysql',
                'host' => env('UPSTREAM_DB_HOST', env('DB_HOST', '127.0.0.1')),
                'port' => env('DB_PORT', '3306'),
                'database' => env('UPSTREAM_DB_DATABASE', 'nusantara'),
                'username' => env('DB_USERNAME', 'creasico'),
                'password' => env('DB_PASSWORD', 'secret'),
            ],
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (app()->runningInConsole()) {
            $this->commands([
                StatCommand::class,
                ImportCommand::class,
                GenerateStaticCommand::class,
                DistCommand::class,
            ]);
        }

        tap(app()->make('config'), function (Repository $config) {
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

    private function currentBranch(): string
    {
        $branch = env('GIT_BRANCH');

        if (! $branch) {
            $branch = trim(shell_exec('git rev-parse --abbrev-ref HEAD'));
        }

        return (string) str(str_replace('/', '_', $branch))->slug();
    }
}
