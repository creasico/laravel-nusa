<?php

declare(strict_types=1);

namespace Creasi\Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use WithWorkbench;

    protected function defineEnvironment($app)
    {
        // $app['config']->set('database.default', env('DB_CONNECTION'));
    }
}
