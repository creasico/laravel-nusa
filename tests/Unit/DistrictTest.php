<?php

declare(strict_types=1);

use Creasi\Nusa\Contracts\District as DistrictContract;
use Creasi\Nusa\Contracts\Province;
use Creasi\Nusa\Contracts\Regency;
use Creasi\Nusa\Contracts\Village;
use Creasi\Nusa\Models\District;
use Creasi\Tests\TestCase;
use Illuminate\Support\Collection;

dataset('searchProvider', fn () => [
    'by name' => ['Pekalongan'],
]);

it('searches districts by name', function (string $keyword) {
    /** @var TestCase $this */
    expect(District::search($keyword)->first())->toBeInstanceOf(DistrictContract::class);
})->with('searchProvider');

it('exposes district attributes and casts', function () {
    /** @var TestCase $this */
    $districts = District::query()->take(5)->get();

    expect($districts)->not->toBeEmpty();

    foreach ($districts as $district) {
        expect($district->code)->toBeString()
            ->and($district->latitude)->toBeFloat()
            ->and($district->longitude)->toBeFloat()
            ->and($district->postal_codes)->toBeInstanceOf(Collection::class)
            ->and($district)->toBeInstanceOf(DistrictContract::class);
    }
});

it('belongs to a province', function () {
    /** @var TestCase $this */
    $district = District::query()->firstOrFail();

    expect($district->province)->toBeInstanceOf(Province::class);
});

it('belongs to a regency', function () {
    /** @var TestCase $this */
    $district = District::query()->firstOrFail();

    expect($district->regency)->toBeInstanceOf(Regency::class);
});

it('has many villages', function () {
    /** @var TestCase $this */
    $district = District::query()->firstOrFail();

    $villages = $district->villages()->take(10)->get();

    expect($villages)->not->toBeEmpty();
    expect($villages->every(fn ($village) => $village instanceof Village))->toBeTrue();
});

it('exposes villages as subdivisions', function () {
    /** @var TestCase $this */
    $district = District::query()->firstOrFail();

    $subdivisions = $district->subdivisions();

    expect($subdivisions)->toBeInstanceOf(Collection::class);
    expect($subdivisions)->not->toBeEmpty();
    expect($subdivisions->every(fn ($village) => $village instanceof Village))->toBeTrue();
});
