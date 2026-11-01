<?php

declare(strict_types=1);

use Creasi\Nusa\Contracts\Address as AddressContract;
use Creasi\Nusa\Contracts\District;
use Creasi\Nusa\Contracts\Province;
use Creasi\Nusa\Contracts\Regency;
use Creasi\Nusa\Models\Village;
use Creasi\Tests\Fixture\HasManyAddresses;
use Creasi\Tests\Fixture\HasOneAddress;
use Creasi\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('associates an address with a village', function () {
    /** @var TestCase $this */
    $village = Village::query()->firstOrFail();
    $address = $this->createAddress([
        'line' => $this->faker->streetAddress(),
    ]);

    $address = $address->associateWith($village);

    expect($address->village)->toBeInstanceOf(Creasi\Nusa\Contracts\Village::class)
        ->and($address->district)->toBeInstanceOf(District::class)
        ->and($address->regency)->toBeInstanceOf(Regency::class)
        ->and($address->province)->toBeInstanceOf(Province::class)
        ->and($address->addressable)->toBeNull()
        ->and($address->village_code)->toBe($village->code);
});

it('may have many addresses', function () {
    /** @var TestCase $this */
    $addressable = HasManyAddresses::create();

    $addressable->addresses()->save(
        $this->createAddress(['line' => 'Coba Alamat'])
    );

    expect($addressable->addresses)->toHaveCount(1);
});

it('may have one address', function () {
    /** @var TestCase $this */
    $addressable = HasOneAddress::create();

    $addressable->address()->save(
        $this->createAddress(['line' => 'Coba Alamat'])
    );

    expect($addressable->address)->toBeInstanceOf(AddressContract::class);
});
