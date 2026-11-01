<?php

declare(strict_types=1);

use Creasi\Nusa\Contracts\District;
use Creasi\Nusa\Contracts\Province;
use Creasi\Nusa\Contracts\Regency;
use Creasi\Nusa\Contracts\Village as VillageContract;
use Creasi\Nusa\Models\Village;
use Creasi\Tests\TestCase;

dataset('searchProvider', fn () => [
    'by name' => ['Kraton'],
]);

it('searches villages by name', function (string $keyword) {
    /** @var TestCase $this */
    expect(Village::search($keyword)->first())->toBeInstanceOf(VillageContract::class);
})->with('searchProvider');

it('exposes village attributes and casts', function () {
    /** @var TestCase $this */
    $villages = Village::query()
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->take(5)
        ->get();

    expect($villages)->not->toBeEmpty();

    foreach ($villages as $village) {
        expect($village->code)->toBeString()
            ->and($village->latitude)->toBeFloat()
            ->and($village->longitude)->toBeFloat()
            ->and($village)->toBeInstanceOf(VillageContract::class);

        if ($village->postal_code !== null) {
            expect($village->postal_code)->toBeInt();
        }
    }
});

it('belongs to a province', function () {
    /** @var TestCase $this */
    $village = Village::query()->firstOrFail();

    expect($village->province)->toBeInstanceOf(Province::class);
});

it('belongs to a regency', function () {
    /** @var TestCase $this */
    $village = Village::query()->firstOrFail();

    expect($village->regency)->toBeInstanceOf(Regency::class);
});

it('belongs to a district', function () {
    /** @var TestCase $this */
    $village = Village::query()->firstOrFail();

    expect($village->district)->toBeInstanceOf(District::class);
});
