<?php

declare(strict_types=1);

namespace Creasi\Tests;

use Creasi\Nusa\Contracts\Address as AddressContract;
use Creasi\Nusa\Models\Address;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use WithFaker;
    use WithWorkbench;

    protected function defineEnvironment($app)
    {
        if ($directory = env('NUSA_DATABASE_DIR')) {
            $app['config']->set('database.connections.nusa', array_merge(
                (array) $app['config']->get('database.connections.nusa', []),
                ['database' => rtrim($directory, \DIRECTORY_SEPARATOR).\DIRECTORY_SEPARATOR.'nusa.sqlite'],
            ));
        }

        if ($prefix = env('NUSA_TEST_ROUTES_PREFIX')) {
            $app['config']->set('creasi.nusa.routes_prefix', $prefix);
        }

        if (env('NUSA_TEST_ROUTES_DISABLED')) {
            $app['config']->set('creasi.nusa.routes_enable', false);
        }

        if ($addressable = env('NUSA_TEST_ADDRESSABLE')) {
            $app['config']->set('creasi.nusa.addressable', $addressable);
        }
    }

    protected function createAddress(array $attributes): Address
    {
        return $this->app->make(AddressContract::class)->create($attributes);
    }
}
