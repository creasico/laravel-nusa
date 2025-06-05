<?php

namespace Creasi\Tests\Features;

use Creasi\Tests\Models\ProvinceTest as ModelTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
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
            'include regencies' => ['regencies'],
            'include districts' => ['districts'],
            'include villages' => ['villages'],
        ];
    }

    public static function possibleSearchRegencies(): array
    {
        return [
            'no search' => [],
            'with keyword' => ['pekalongan'],
        ];
    }

    public static function possibleSearchDistricts(): array
    {
        return [
            'no search' => [],
            'with keyword' => ['kedung'],
        ];
    }

    public static function possibleSearchVillages(): array
    {
        return [
            'no search' => [],
            'with keyword' => ['dukuh'],
        ];
    }

    public static function invalidCodes(): array
    {
        return [
            // 'array of non-numeric code' => [['foo']],
            'non-array of numeric code' => [33],
        ];
    }

    #[Test]
    #[DependsOnClass(ModelTest::class)]
    #[DataProvider('availableQueries')]
    public function it_shows_all_available_provinces(?string ...$with): void
    {
        $response = $this->getJson($this->path(query: [
            'with' => $with,
        ]));

        $this->assertCollectionResponse($response, self::FIELDS, $with);
    }

    #[Test]
    #[Depends('it_shows_all_available_provinces')]
    #[DataProvider('invalidCodes')]
    public function it_shows_errors_for_invalid_codes(mixed $codes): void
    {
        $response = $this->getJson($this->path(query: [
            'codes' => $codes,
        ]));

        $response->assertUnprocessable();
    }

    #[Test]
    #[Depends('it_shows_all_available_provinces')]
    public function it_shows_provinces_by_selected_codes(): void
    {
        $response = $this->getJson($this->path(query: [
            'codes' => [33, 32],
        ]));

        $this->assertCollectionResponse($response, self::FIELDS)->assertJsonCount(2, 'data');
    }

    #[Test]
    #[Depends('it_shows_all_available_provinces')]
    public function it_shows_provinces_by_search_query(): void
    {
        $response = $this->getJson($this->path(query: [
            'search' => 'Jawa Tengah',
        ]));

        $this->assertCollectionResponse($response, self::FIELDS)->assertJsonCount(1, 'data');
    }

    #[Test]
    #[Depends('it_shows_all_available_provinces')]
    #[DataProvider('availableQueries')]
    public function it_shows_single_province(?string ...$with): void
    {
        $response = $this->getJson($this->path('33', [
            'with' => $with,
        ]));

        $this->assertSingleResponse($response, self::FIELDS, $with);
    }

    #[Test]
    #[Depends('it_shows_all_available_provinces')]
    #[DataProvider('possibleSearchRegencies')]
    public function it_shows_available_regencies_in_a_province(?string $search): void
    {
        $response = $this->getJson($this->path('33/regencies', [
            'search' => $search,
        ]));

        $this->assertCollectionResponse($response, RegencyTest::FIELDS);
    }

    #[Test]
    #[Depends('it_shows_all_available_provinces')]
    #[DataProvider('possibleSearchDistricts')]
    public function it_shows_available_districts_in_a_province(?string $search): void
    {
        $response = $this->getJson($this->path('33/districts', [
            'search' => $search,
        ]));

        $this->assertCollectionResponse($response, DistrictTest::FIELDS);
    }

    #[Test]
    #[Depends('it_shows_all_available_provinces')]
    #[DataProvider('possibleSearchVillages')]
    public function it_shows_available_villages_in_a_province(?string $search): void
    {
        $response = $this->getJson($this->path('33/villages', [
            'search' => $search,
        ]));

        $this->assertCollectionResponse($response, VillageTest::FIELDS);
    }
}
