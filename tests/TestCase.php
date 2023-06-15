<?php

namespace Creasi\Tests;

use Creasi\Laravel\Facades\Package;
use Creasi\Laravel\Package\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }
}
