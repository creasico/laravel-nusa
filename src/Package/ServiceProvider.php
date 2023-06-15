<?php

namespace Creasi\Laravel\Package;

use Creasi\Laravel\Package;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    private const LIB_PATH = __DIR__.'/../..';

    public function boot()
    {
        // .
    }

    public function register()
    {
        $this->mergeConfigFrom(self::LIB_PATH.'/config/package.php', 'creasi.package');

        $this->app->bind('creasi.package', function () {
            return new class {
                public function lorem()
                {
                    return 'Lorem ipsum';
                }
            };
        });

        if ($this->app->runningInConsole()) {
            $this->registerPublishables();

            $this->registerCommands();
        }
    }

    protected function registerPublishables()
    {
        $this->publishes([
            self::LIB_PATH.'/config/package.php' => \config_path('package.php'),
        ], 'creasi-config');

        $timestamp = date('Y_m_d_His', time());
        $migrations = self::LIB_PATH.'/database/migrations';

        $this->publishes([
            $migrations.'/create_package_table.php' => database_path('migrations/'.$timestamp.'_create_package_table.php'),
        ], 'creasi-migrations');

        $this->loadMigrationsFrom($migrations);
    }

    protected function registerCommands()
    {
        $this->commands([]);
    }
}
