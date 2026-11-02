<?php

declare(strict_types=1);

use Creasi\Nusa\Models\Province;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

it('returns distinct postal codes for a province', function () {
    test_flush_cache();

    $province = Province::query()->firstOrFail();
    $codes = $province->postal_codes;

    expect($codes)->toBeInstanceOf(Collection::class)
        ->and($codes)->not->toBeEmpty()
        ->and($codes->duplicates())->toBeEmpty()
        ->and(Cache::has('postal_codes:'.$province->code))->toBeTrue();
});

it('returns an empty collection for a province without villages', function () {
    test_flush_cache();

    $province = new Province(['code' => '99', 'name' => 'Empty Province']);

    expect($province->postal_codes)->toBeEmpty();
});

it('hides the distinct villages relation', function () {
    test_flush_cache();

    $province = Province::query()->with('distinctVillagesByPostalCodes')->firstOrFail();
    $array = $province->toArray();

    expect(array_keys($array))->not->toContain('distinct_villages_by_postal_codes')
        ->and(array_keys($array))->not->toContain('distinctVillagesByPostalCodes');
});
