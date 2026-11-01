<?php

declare(strict_types=1);

use Creasi\Nusa\Contracts\Address;
use Creasi\Nusa\Contracts\District;
use Creasi\Nusa\Contracts\Province;
use Creasi\Nusa\Contracts\Regency;
use Creasi\Nusa\Contracts\Village;
use Creasi\Nusa\Models\District as DistrictModel;
use Creasi\Nusa\Models\Province as ProvinceModel;
use Creasi\Nusa\Models\Regency as RegencyModel;
use Creasi\Nusa\Models\Village as VillageModel;
use Creasi\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Concurrency;

it('registers the workbench commands', function () {
    /** @var TestCase $this */
    expect(Artisan::all())->toHaveKeys([
        'nusa:import',
        'nusa:dist',
        'nusa:stat',
        'nusa:generate-static',
    ]);
});

it('configures the workbench application', function () {
    /** @var TestCase $this */
    $database = config('database.connections.nusa.database');

    expect(config('app.locale'))->toBe('id')
        ->and(config('app.faker_locale'))->toBe('id_ID')
        ->and($database)->toStartWith(\dirname(__DIR__, 2).\DIRECTORY_SEPARATOR.'database')
        ->and($database)->toEndWith('-full.sqlite')
        ->and(config('database.connections.current.database'))->toEndWith('nusa.sqlite');
});

it('uses the fork concurrency driver', function () {
    /** @var TestCase $this */
    expect(Concurrency::getDefaultInstance())->toBe('fork');
});

it('binds the nusa contracts', function (string $contract, string $model) {
    /** @var TestCase $this */
    expect($this->app->make($contract))->toBeInstanceOf($model);
})->with([
    'province' => [Province::class, ProvinceModel::class],
    'regency' => [Regency::class, RegencyModel::class],
    'district' => [District::class, DistrictModel::class],
    'village' => [Village::class, VillageModel::class],
    'address' => [Address::class, Creasi\Nusa\Models\Address::class],
]);
