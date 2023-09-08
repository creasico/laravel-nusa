<?php

declare(strict_types=1);

namespace Creasi\Nusa;

use Creasi\Nusa\Console\SyncCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    private const LIB_PATH = __DIR__.'/..';

    public function boot()
    {
        if (app()->runningInConsole()) {
            $this->registerPublishables();

            $this->registerCommands();
        }

        $this->defineRoutes();
    }

    public function register()
    {
        config([
            'database.connections.nusa' => array_merge([
                'driver' => 'sqlite',
                'database' => self::LIB_PATH.'/database/nusa.sqlite',
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
            ->group(self::LIB_PATH.'/routes/nusa.php');
    }

    protected function registerPublishables()
    {
        $this->publishes([
            self::LIB_PATH.'/config/nusa.php' => \config_path('creasi/nusa.php'),
        ], ['creasi-config', 'creasi-nusa-config']);
    }

    protected function registerBindings()
    {
        $this->app->bind(Contracts\Address::class, function ($app) {
            $addressable = config('creasi.nusa.addressable');

            return $app->make($addressable);
        });

        $this->app->bind(Contracts\Province::class, function ($app) {
            return $app->make(Models\Province::class);
        });

        $this->app->bind(Contracts\Regency::class, function ($app) {
            return $app->make(Models\Regency::class);
        });

        $this->app->bind(Contracts\District::class, function ($app) {
            return $app->make(Models\District::class);
        });

        $this->app->bind(Contracts\Village::class, function ($app) {
            return $app->make(Models\Village::class);
        });
    }

    protected function registerCommands()
    {
        $this->commands([
            SyncCommand::class,
        ]);
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
