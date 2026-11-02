<?php

declare(strict_types=1);

use Creasi\Nusa\Contracts\District;
use Creasi\Nusa\Contracts\Province as ProvinceContract;
use Creasi\Nusa\Contracts\Regency;
use Creasi\Nusa\Contracts\Village;
use Creasi\Nusa\Models\Province;
use Creasi\Tests\TestCase;
use Illuminate\Support\Collection;

dataset('searchProvider', fn () => [
    'by name' => ['Jawa'],
]);

it('searches provinces by name', function (string $keyword) {
    /** @var TestCase $this */
    expect(Province::search($keyword)->first())->toBeInstanceOf(ProvinceContract::class);
})->with('searchProvider');

it('exposes province attributes and casts', function () {
    /** @var TestCase $this */
    $provinces = Province::query()
        ->with('distinctVillagesByPostalCodes')
        ->take(5)
        ->get();

    expect($provinces)->not->toBeEmpty();

    foreach ($provinces as $province) {
        expect($province->code)->toBeString()
            ->and($province->latitude)->toBeFloat()
            ->and($province->longitude)->toBeFloat()
            ->and($province->postal_codes)->toBeInstanceOf(Collection::class)
            ->and($province)->toBeInstanceOf(ProvinceContract::class);
    }
});

it('has many regencies', function () {
    /** @var TestCase $this */
    $province = Province::query()->firstOrFail();

    $regencies = $province->regencies()->take(10)->get();

    expect($regencies)->not->toBeEmpty();
    expect($regencies->every(fn ($regency) => $regency instanceof Regency))->toBeTrue();
});

it('has many districts', function () {
    /** @var TestCase $this */
    $province = Province::query()->firstOrFail();

    $districts = $province->districts()->take(10)->get();

    expect($districts)->not->toBeEmpty();
    expect($districts->every(fn ($district) => $district instanceof District))->toBeTrue();
});

it('has many villages', function () {
    /** @var TestCase $this */
    $province = Province::query()->firstOrFail();

    $villages = $province->villages()->take(10)->get();

    expect($villages)->not->toBeEmpty();
    expect($villages->every(fn ($village) => $village instanceof Village))->toBeTrue();
});

it('exposes regencies as subdivisions', function () {
    /** @var TestCase $this */
    $province = Province::query()->firstOrFail();

    $subdivisions = $province->subdivisions();

    expect($subdivisions)->toBeInstanceOf(Collection::class);
    expect($subdivisions)->not->toBeEmpty();
    expect($subdivisions->every(fn ($regency) => $regency instanceof Regency))->toBeTrue();
});
