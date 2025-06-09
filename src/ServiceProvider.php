<?php

declare(strict_types=1);

namespace Creasi\Nusa;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    private const LIB_PATH = __DIR__.'/..';

    public function boot()
    {
        if (app()->runningInConsole()) {
            $this->registerPublishables();
        }

        $this->loadTranslationsFrom(self::LIB_PATH.'/resources/lang', 'creasico');

        $this->defineRoutes();
    }

    public function register()
    {
        config([
            'database.connections.nusa' => array_merge([
                'driver' => 'sqlite',
                'database' => realpath(self::LIB_PATH.'/database/nusa.sqlite'),
                'foreign_key_constraints' => true,
            ], config('database.connections.nusa', [])),
        ]);

        if (! app()->configurationIsCached()) {
            $this->mergeConfigFrom(self::LIB_PATH.'/config/nusa.php', 'creasi.nusa');
        }

        $this->registerBindings();
    }

    protected function defineRoutes()
    {
        if (app()->routesAreCached() && config('creasi.nusa.routes_enable') === false) {
            return;
        }

        Route::prefix(config('creasi.nusa.routes_prefix', 'nusa'))
            ->name('nusa.')
            ->group(self::LIB_PATH.'/routes/nusa.php');
    }

    protected function registerPublishables()
    {
        $this->publishes([
            self::LIB_PATH.'/config/nusa.php' => \config_path('creasi/nusa.php'),
        ], ['creasi-config', 'creasi-nusa-config']);

        $this->publishes([
            self::LIB_PATH.'/resources/lang' => \resource_path('lang/vendor/creasico'),
        ], ['creasi-lang']);

        $this->publishes([
            self::LIB_PATH.'/database/migrations/create_addresses_tables.php.stub' => $this->getMigrationFileName('create_addresses_tables.php'),
        ], 'creasi-migrations');
    }

    protected function registerBindings()
    {
        $this->app->bind(Contracts\Address::class, function ($app) {
            $addressable = config('creasi.nusa.addressable');

            return $app->make($addressable);
        });

        $this->app->bind(Contracts\Province::class, Models\Province::class);
        $this->app->bind(Contracts\Regency::class, Models\Regency::class);
        $this->app->bind(Contracts\District::class, Models\District::class);
        $this->app->bind(Contracts\Village::class, Models\Village::class);
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @link https://github.com/spatie/laravel-permission/blob/main/src/PermissionServiceProvider.php
     */
    protected function getMigrationFileName(string $migrationFileName): string
    {
        $filesystem = app()->make(Filesystem::class);
        $migrationPath = app()->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR;

        return Collection::make([$migrationPath])
            ->flatMap(fn ($path) => $filesystem->glob($path.'*_'.$migrationFileName))
            ->push($migrationPath.date('Y_m_d_His').'_'.$migrationFileName)
            ->first();
    }

    public function provides()
    {
        return [
            Contracts\Address::class,
            Contracts\Province::class,
            Contracts\Regency::class,
            Contracts\District::class,
            Contracts\Village::class,
        ];
    }
}
