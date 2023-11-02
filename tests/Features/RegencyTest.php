<?php

namespace Creasi\Tests\Features;

use Creasi\Tests\Models\RegencyTest as ModelTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('api')]
#[Group('regencies')]
class RegencyTest extends TestCase
{
    public const FIELDS = [
        'code',
        'name',
        'province_code',
        // 'latitude',
        // 'longitude',
        // 'coordinates'
    ];

    protected $path = 'nusa/regencies';

    public static function availableQueries(): array
    {
        return [
            'basic request' => [],
            'include postal_codes' => ['postal_codes'],
            'include coordinates' => ['coordinates'],
            'include province' => ['province'],
            'include districts' => ['districts'],
            'include villages' => ['villages'],
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
            'array of non-numeric code' => [['foo']],
            'non-array of numeric code' => [3375],
        ];
    }

    #[Test]
    #[DependsOnClass(ModelTest::class)]
    #[DependsOnClass(ProvinceTest::class)]
    #[DataProvider('availableQueries')]
    public function it_shows_all_available_regencies(?string ...$with): void
    {
        $response = $this->getJson($this->path(query: [
            'with' => $with,
        ]));

        $this->assertCollectionResponse($response, self::FIELDS, $with);
    }

    #[Test]
    #[Depends('it_shows_all_available_regencies')]
    #[DataProvider('invalidCodes')]
    public function it_shows_errors_for_invalid_codes(mixed $codes): void
    {
        $response = $this->getJson($this->path(query: [
            'codes' => $codes,
        ]));

        $response->assertUnprocessable();
    }

    #[Test]
    #[Depends('it_shows_all_available_regencies')]
    public function it_shows_regencies_by_selected_codes(): void
    {
        $response = $this->getJson($this->path(query: [
            'codes' => [3375, 3325],
        ]));

        $this->assertCollectionResponse($response, self::FIELDS)->assertJsonCount(2, 'data');
    }

    #[Test]
    #[Depends('it_shows_all_available_regencies')]
    public function it_shows_regencies_by_search_query(): void
    {
        $response = $this->getJson($this->path(query: [
            'search' => 'Pekalongan',
        ]));

        $this->assertCollectionResponse($response, self::FIELDS)->assertJsonCount(2, 'data');
    }

    #[Test]
    #[Depends('it_shows_all_available_regencies')]
    #[DataProvider('availableQueries')]
    public function it_shows_single_regency(?string ...$with): void
    {
        $response = $this->getJson($this->path('3375', [
            'with' => $with,
        ]));

        $this->assertSingleResponse($response, self::FIELDS, $with);
    }

    #[Test]
    #[Depends('it_shows_all_available_regencies')]
    #[DataProvider('possibleSearchDistricts')]
    public function it_shows_available_districts_in_a_regency(?string $search): void
    {
        $response = $this->getJson($this->path('3326/districts', [
            'search' => $search,
        ]));

        $this->assertCollectionResponse($response, DistrictTest::FIELDS);
    }

    #[Test]
    #[Depends('it_shows_all_available_regencies')]
    #[DataProvider('possibleSearchVillages')]
    public function it_shows_available_villages_in_a_regency(?string $search): void
    {
        $response = $this->getJson($this->path('3375/villages', [
            'search' => $search,
        ]));

        $this->assertCollectionResponse($response, VillageTest::FIELDS);
    }
}
