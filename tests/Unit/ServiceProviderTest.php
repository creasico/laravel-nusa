<?php

declare(strict_types=1);

use Creasi\Nusa\Contracts\Address as AddressContract;
use Creasi\Nusa\Contracts\District;
use Creasi\Nusa\Contracts\Province as ProvinceContract;
use Creasi\Nusa\Contracts\Regency;
use Creasi\Nusa\Contracts\Village;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\ServiceProvider as NusaServiceProvider;
use Creasi\Tests\Fixture\CustomAddress;
use Creasi\Tests\TestCase;
use Illuminate\Support\Facades\Route;

it('registers the default routes', function () {
    expect(Route::has('nusa.provinces.index'))->toBeTrue()
        ->and(route('nusa.provinces.index'))->toContain('/nusa/provinces');
});

it('uses a custom route prefix', function () {
    /** @var TestCase $this */
    $previous = getenv('NUSA_TEST_ROUTES_PREFIX');

    try {
        set_test_env('NUSA_TEST_ROUTES_PREFIX', 'wilayah');
        $this->refreshApplication();

        expect(route('nusa.provinces.index'))->toContain('/wilayah/provinces');
    } finally {
        set_test_env('NUSA_TEST_ROUTES_PREFIX', $previous === false ? null : $previous);
        $this->refreshApplication();
    }
});

it('can disable routes', function () {
    /** @var TestCase $this */
    $previous = getenv('NUSA_TEST_ROUTES_DISABLED');

    try {
        set_test_env('NUSA_TEST_ROUTES_DISABLED', 'true');
        $this->refreshApplication();

        expect(Route::has('nusa.provinces.index'))->toBeFalse();
    } finally {
        set_test_env('NUSA_TEST_ROUTES_DISABLED', $previous === false ? null : $previous);
        $this->refreshApplication();
    }
});

it('resolves a custom addressable model', function () {
    /** @var TestCase $this */
    config(['creasi.nusa.addressable' => CustomAddress::class]);

    expect($this->app->make(AddressContract::class))->toBeInstanceOf(CustomAddress::class);
});

it('uses the configured connection', function () {
    $previous = config('creasi.nusa.connection');

    try {
        config(['creasi.nusa.connection' => 'testing']);

        expect((new Province)->getConnectionName())->toBe('testing');
    } finally {
        config(['creasi.nusa.connection' => $previous]);
    }
});

it('publishes the config and migration stub', function () {
    /** @var TestCase $this */
    $this->artisan('vendor:publish', ['--tag' => 'creasi-config', '--force' => true])
        ->assertExitCode(0);
    $this->artisan('vendor:publish', ['--tag' => 'creasi-migrations', '--force' => true])
        ->assertExitCode(0);

    expect(file_exists(config_path('creasi/nusa.php')))->toBeTrue();

    $migrations = glob(database_path('migrations/*_create_addresses_tables.php'));

    expect($migrations)->not->toBeEmpty()
        ->and(file_get_contents($migrations[0]))->toContain("Schema::create('addresses'");

    @unlink(config_path('creasi/nusa.php'));
});

it('exposes the nusa contracts', function () {
    /** @var TestCase $this */
    $provider = $this->app->getProvider(NusaServiceProvider::class);

    expect($provider)->not->toBeNull()
        ->and($provider->provides())->toContain(
            AddressContract::class,
            ProvinceContract::class,
            Regency::class,
            District::class,
            Village::class,
        );
});
