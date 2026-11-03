<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\ServiceProvider;
use Workbench\App\Console\DistCommand;
use Workbench\App\Console\GenerateStaticCommand;
use Workbench\App\Console\ImportCommand;
use Workbench\App\Console\StatCommand;
use Workbench\App\Support\GitHelper;

class WorkbenchServiceProvider extends ServiceProvider
{
    use GitHelper;

    /**
     * Register services.
     */
    public function register(): void
    {
        $nusa = config('database.connections.nusa');
        $databaseDir = env('NUSA_DATABASE_DIR') ?: ($nusa['database']
            ? \dirname($nusa['database'])
            : \dirname(__DIR__, 3).'/database');

        config(['creasi.nusa.database_dir' => $databaseDir]);

        if (app()->runningInConsole()) {
            $this->loadMigrationsFrom($databaseDir.'/migrations');
        }

        $branch = $this->currentBranch();
        $dbPaths = [
            "{$databaseDir}/nusa.{$branch}-full.sqlite",
            "{$databaseDir}/nusa.{$branch}.sqlite",
        ];

        if (! $nusa['database']) {
            $dbPaths[] = "{$databaseDir}/nusa.sqlite";
        }

        foreach ($dbPaths as $dbPath) {
            if (file_exists($dbPath)) {
                continue;
            }

            $cachedPath = str_replace($branch, 'main', $dbPath);

            if (file_exists($cachedPath)) {
                rename($cachedPath, $dbPath);

                continue;
            }

            touch($dbPath);
            chmod($dbPath, 0664);
        }

        config([
            'app' => [
                'locale' => 'id',
                'faker_locale' => 'id_ID',
            ],
            'database.connections.current' => array_merge($nusa, [
                'database' => $databaseDir.\DIRECTORY_SEPARATOR.'nusa.sqlite',
            ]),
            'database.connections.nusa' => array_merge($nusa, [
                'database' => $dbPaths[0],
            ]),
            'database.connections.upstream' => [
                'driver' => 'mysql',
                'host' => env('UPSTREAM_DB_HOST', env('DB_HOST', '127.0.0.1')),
                'port' => env('DB_PORT', '3306'),
                'database' => env('UPSTREAM_DB_DATABASE', 'nusantara'),
                'username' => env('DB_USERNAME', 'creasico'),
                'password' => env('DB_PASSWORD', 'secret'),
                'collation' => 'utf8mb4_unicode_ci',
                'charset' => 'utf8mb4',
            ],
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // `@nusaAssets` only ever points at the published file, so the preview needs it
        // to exist. Stands in for `vendor:publish --tag=nusa-assets` during development.
        if (! is_file($asset = public_path('vendor/nusa/nusa.js'))) {
            if (! is_dir($dir = \dirname($asset))) {
                mkdir($dir, 0777, true);
            }

            copy(\dirname(__DIR__, 3).'/resources/js/nusa.js', $asset);
        }

        if (app()->runningInConsole()) {
            $this->commands([
                StatCommand::class,
                ImportCommand::class,
                GenerateStaticCommand::class,
                DistCommand::class,
            ]);
        }

        Concurrency::setDefaultInstance('fork');

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

            // `serve` runs the skeleton app, whose `.env` has no APP_KEY, and neither
            // `env:` in testbench.yaml nor a shell variable reaches the `php -S` child
            // process (ServeCommand::$passthroughVariables is a fixed list). Fake one so
            // the `web` middleware group can boot.
            if (empty($config->get('app.key'))) {
                $config->set('app.key', 'base64:iZ6Mbip8hrj1WDiIXoDkLzj0eR9Q5KFQIJn0PvMAPxU=');
                $config->set('app.cipher', 'AES-256-CBC');
            }
        });
    }

    /**
     * @param  array<string, mixed>  $value
     */
    private function mergeConfig(Repository $config, string $key, array $value): void
    {
        $config->set($key, array_merge((array) $config->get($key, []), $value));
    }
}
