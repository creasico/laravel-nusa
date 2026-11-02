<?php

declare(strict_types=1);

use Creasi\Nusa\Contracts\Address as AddressContract;
use Creasi\Nusa\Contracts\District;
use Creasi\Nusa\Contracts\Province;
use Creasi\Nusa\Contracts\Regency;
use Creasi\Nusa\Models\District as DistrictModel;
use Creasi\Nusa\Models\Province as ProvinceModel;
use Creasi\Nusa\Models\Regency as RegencyModel;
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

it('accepts explicit region overrides', function () {
    /** @var TestCase $this */
    $village = Village::query()->firstOrFail();
    /** @var DistrictModel $district */
    $district = DistrictModel::where('code', '!=', $village->district_code)->firstOrFail();
    /** @var RegencyModel $regency */
    $regency = RegencyModel::where('code', '!=', $village->regency_code)->firstOrFail();
    /** @var ProvinceModel $province */
    $province = ProvinceModel::where('code', '!=', $village->province_code)->firstOrFail();

    $address = $this->createAddress(['line' => 'Coba Alamat'])
        ->associateWith($village, $district, $regency, $province);

    expect($address->district_code)->toBe($district->code)
        ->and($address->regency_code)->toBe($regency->code)
        ->and($address->province_code)->toBe($province->code);
});

it('persists an association for an unsaved address', function () {
    /** @var TestCase $this */
    $village = Village::query()->firstOrFail();
    $address = $this->makeAddress();
    $address->line = 'Alamat Baru';

    $address = $address->associateWith($village);

    expect($address->exists)->toBeTrue()
        ->and($address->village_code)->toBe($village->code);
});

it('resolves the morph addressable', function () {
    /** @var TestCase $this */
    $village = Village::query()->firstOrFail();
    $addressable = HasOneAddress::create();
    $address = $this->createAddress(['line' => 'Coba Alamat'])->associateWith($village);

    $addressable->address()->save($address);

    expect($address->fresh()->addressable)->toBeInstanceOf(HasOneAddress::class);
});

it('exposes address casts and fillable attributes', function () {
    /** @var TestCase $this */
    $address = $this->makeAddress();

    expect($address->getCasts()['postal_code'])->toBe('int')
        ->and($address->getFillable())->toContain(
            'line',
            'postal_code',
            'village_code',
            'district_code',
            'regency_code',
            'province_code',
        );
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
