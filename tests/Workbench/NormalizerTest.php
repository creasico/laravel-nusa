<?php

declare(strict_types=1);

use Creasi\Tests\TestCase;
use Workbench\App\Support\Normalizer;

beforeEach(function () {
    Normalizer::$invalid = [];
});

it('normalizes a province', function () {
    /** @var TestCase $this */
    $data = (new Normalizer('33', 'jawa tengah', null, -6.9, 110.4))->normalize();

    expect($data)->toMatchArray([
        'code' => 33,
        'name' => 'Jawa Tengah',
        'latitude' => -6.9,
        'longitude' => 110.4,
        'coordinates' => null,
    ]);
});

it('normalizes a regency', function () {
    /** @var TestCase $this */
    $data = (new Normalizer('33.75', 'kota pekalongan', '51111', -6.88, 109.675))->normalize();

    expect($data)->toMatchArray([
        'code' => '33.75',
        'province_code' => '33',
        'name' => 'Kota Pekalongan',
    ]);
});

it('normalizes a district', function () {
    /** @var TestCase $this */
    $data = (new Normalizer('33.75.03', 'Pekalongan Utara', null, -6.88, 109.675))->normalize();

    expect($data)->toMatchArray([
        'code' => '33.75.03',
        'regency_code' => '33.75',
        'province_code' => '33',
        'name' => 'Pekalongan Utara',
    ]);
});

it('normalizes a village', function () {
    /** @var TestCase $this */
    $data = (new Normalizer('33.75.03.1006', 'Padukuhan Kraton', '51111', -6.88, 109.675))->normalize();

    expect($data)->toMatchArray([
        'code' => '33.75.03.1006',
        'district_code' => '33.75.03',
        'regency_code' => '33.75',
        'province_code' => '33',
        'name' => 'Padukuhan Kraton',
        'postal_code' => '51111',
    ]);
});

it('swaps coordinate pairs from upstream', function () {
    /** @var TestCase $this */
    $coordinates = json_encode([[[110.0, -6.9], [110.1, -6.9], [110.1, -7.0], [110.0, -6.9]]]);

    $data = (new Normalizer('33', 'Jawa Tengah', null, -6.9, 110.0, $coordinates))->normalize();

    expect(json_decode($data['coordinates'], true))->toBe([[[-6.9, 110], [-6.9, 110.1], [-7, 110.1], [-6.9, 110]]]);
});

it('swaps a bare coordinate pair from upstream', function () {
    /** @var TestCase $this */
    $coordinates = json_encode([110.0, -6.9]);

    $data = (new Normalizer('33', 'Jawa Tengah', null, -6.9, 110.0, $coordinates))->normalize();

    expect(json_decode($data['coordinates'], true))->toBe([-6.9, 110]);
});

it('ignores invalid codes', function () {
    /** @var TestCase $this */
    $normalizer = new Normalizer('123', 'Invalid', null);

    expect($normalizer->normalize())->toBeNull()
        ->and($normalizer->type)->toBeNull()
        ->and(Normalizer::$invalid)->toBe([['123', 'Invalid']]);
});
