<?php

declare(strict_types=1);

namespace Creasi\Nusa;

use Creasi\Nusa\Console\SyncCommand;
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
    }

    protected function registerPublishables()
    {
        $this->publishes([
            self::LIB_PATH.'/config/nusa.php' => \config_path('creasi/nusa.php'),
        ], ['creasi-config', 'creasi-nusa-config']);

        // $this->loadMigrationsFrom(self::LIB_PATH.'/database/migrations');
    }

    protected function registerCommands()
    {
        $this->commands([
            SyncCommand::class,
        ]);
    }
}
