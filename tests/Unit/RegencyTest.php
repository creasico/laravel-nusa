<?php

declare(strict_types=1);

use Creasi\Nusa\Contracts\District;
use Creasi\Nusa\Contracts\Province;
use Creasi\Nusa\Contracts\Regency as RegencyContract;
use Creasi\Nusa\Contracts\Village;
use Creasi\Nusa\Models\Regency;
use Creasi\Tests\TestCase;
use Illuminate\Support\Collection;

dataset('searchProvider', fn () => [
    'by name' => ['Pekalongan'],
]);

it('searches regencies by name', function (string $keyword) {
    /** @var TestCase $this */
    expect(Regency::search($keyword)->first())->toBeInstanceOf(RegencyContract::class);
})->with('searchProvider');

it('exposes regency attributes and casts', function () {
    /** @var TestCase $this */
    $regencies = Regency::query()->take(5)->get();

    expect($regencies)->not->toBeEmpty();

    foreach ($regencies as $regency) {
        expect($regency->code)->toBeString()
            ->and($regency->latitude)->toBeFloat()
            ->and($regency->longitude)->toBeFloat()
            ->and($regency->postal_codes)->toBeInstanceOf(Collection::class)
            ->and($regency)->toBeInstanceOf(RegencyContract::class);
    }
});

it('belongs to a province', function () {
    /** @var TestCase $this */
    $regency = Regency::query()->firstOrFail();

    expect($regency->province)->toBeInstanceOf(Province::class);
});

it('has many districts', function () {
    /** @var TestCase $this */
    $regency = Regency::query()->firstOrFail();

    $districts = $regency->districts()->take(10)->get();

    expect($districts)->not->toBeEmpty();
    expect($districts->every(fn ($district) => $district instanceof District))->toBeTrue();
});

it('has many villages', function () {
    /** @var TestCase $this */
    $regency = Regency::query()->firstOrFail();

    $villages = $regency->villages()->take(10)->get();

    expect($villages)->not->toBeEmpty();
    expect($villages->every(fn ($village) => $village instanceof Village))->toBeTrue();
});

it('exposes districts as subdivisions', function () {
    /** @var TestCase $this */
    $regency = Regency::query()->firstOrFail();

    $subdivisions = $regency->subdivisions();

    expect($subdivisions)->toBeInstanceOf(Collection::class);
    expect($subdivisions)->not->toBeEmpty();
    expect($subdivisions->every(fn ($district) => $district instanceof District))->toBeTrue();
});
