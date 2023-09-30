<?php

namespace Creasi\Tests\Features;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('api')]
#[Group('provinces')]
class ProvinceTest extends TestCase
{
    public const FIELDS = [
        'code',
        'name',
        // 'latitude',
        // 'longitude',
        // 'coordinates'
    ];

    protected $path = 'nusa/provinces';

    public static function availableQueries(): array
    {
        return [
            'basic request' => [],
            'include postal_codes' => ['postal_codes'],
            'include coordinates' => ['coordinates'],
        ];
    }

    public static function invalidCodes(): array
    {
        return [
            'array of non-numeric code' => [['foo']],
            'non-array of numeric code' => [33],
        ];
    }

    #[Test]
    #[DataProvider('availableQueries')]
    public function it_shows_all_available_provinces(?string ...$with)
    {
        $response = $this->getJson($this->path(query: [
            'with' => $with,
        ]));

        $response->assertOk()->assertJsonStructure([
            'data' => [self::FIELDS],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'],
        ]);
    }

    #[Test]
    #[Depends('it_shows_all_available_provinces')]
    public function it_shows_provinces_by_selected_codes()
    {
        $response = $this->getJson($this->path(query: [
            'codes' => [33, 32],
        ]));

        $response->assertOk()->assertJsonStructure([
            'data' => [self::FIELDS],
            'meta' => [],
        ])->assertJsonCount(2, 'data');
    }

    #[Test]
    #[Depends('it_shows_all_available_provinces')]
    #[DataProvider('invalidCodes')]
    public function it_shows_errors_for_invalid_codes(mixed $codes)
    {
        $response = $this->getJson($this->path(query: [
            'codes' => $codes,
        ]));

        $response->assertUnprocessable();
    }

    #[Test]
    #[Depends('it_shows_all_available_provinces')]
    public function it_shows_provinces_by_search_query()
    {
        $response = $this->getJson($this->path(query: [
            'search' => 'Jawa Tengah',
        ]));

        $response->assertOk()->assertJsonStructure([
            'data' => [self::FIELDS],
            'meta' => [],
        ])->assertJsonCount(1, 'data');
    }

    #[Test]
    #[Depends('it_shows_all_available_provinces')]
    #[DataProvider('availableQueries')]
    public function it_shows_single_province(?string ...$with)
    {
        $response = $this->getJson($this->path('33', [
            'with' => $with,
        ]));

        $response->assertOk()->assertJsonStructure([
            'data' => self::FIELDS,
            'meta' => [],
        ]);
    }

    #[Test]
    #[Depends('it_shows_all_available_provinces')]
    public function it_shows_available_regencies_in_a_province()
    {
        $response = $this->getJson($this->path('33/regencies'));

        $response->assertOk()->assertJsonStructure([
            'data' => [RegencyTest::FIELDS],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'],
        ]);
    }

    #[Test]
    #[Depends('it_shows_all_available_provinces')]
    public function it_shows_available_districts_in_a_province()
    {
        $response = $this->getJson($this->path('33/districts'));

        $response->assertOk()->assertJsonStructure([
            'data' => [DistrictTest::FIELDS],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'],
        ]);
    }

    #[Test]
    #[Depends('it_shows_all_available_provinces')]
    public function it_shows_available_villages_in_a_province()
    {
        $response = $this->getJson($this->path('33/villages'));

        $response->assertOk()->assertJsonStructure([
            'data' => [VillageTest::FIELDS],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'],
        ]);
    }
}
