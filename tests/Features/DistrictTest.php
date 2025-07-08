<?php

namespace Creasi\Tests\Features;

use Creasi\Tests\Models\DistrictTest as ModelTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('api')]
#[Group('districts')]
class DistrictTest extends TestCase
{
    public const FIELDS = [
        'code',
        'name',
        'regency_code',
        'province_code',
        'latitude',
        'longitude',
    ];

    protected $path = 'nusa/districts';

    public static function availableQueries(): array
    {
        return [
            'basic request' => [],
            'include province' => ['province'],
            'include regency' => ['regency'],
            'include villages' => ['villages'],
        ];
    }

    public static function possibleSearchVillages(): array
    {
        return [
            'no search' => [null],
            'with keyword' => ['dukuh'],
        ];
    }

    public static function invalidCodes(): array
    {
        return [
            // 'array of non-numeric code' => [['foo']],
            'non-array of numeric code' => ['33.75.03'],
        ];
    }

    #[Test]
    #[DependsOnClass(ModelTest::class)]
    #[DependsOnClass(RegencyTest::class)]
    #[DataProvider('availableQueries')]
    public function it_shows_available_districts(?string ...$with): void
    {
        $response = $this->getJson($this->path(query: [
            'with' => $with,
        ]));

        $this->assertCollectionResponse($response, self::FIELDS, $with);
    }

    #[Test]
    #[Depends('it_shows_available_districts')]
    #[DataProvider('invalidCodes')]
    public function it_shows_errors_for_invalid_codes(mixed $codes): void
    {
        $response = $this->getJson($this->path(query: [
            'codes' => $codes,
        ]));

        $response->assertUnprocessable();
    }

    #[Test]
    #[Depends('it_shows_available_districts')]
    public function it_shows_districts_by_selected_codes(): void
    {
        $response = $this->getJson($this->path(query: [
            'codes' => ['33.75.03', '33.75.04'],
        ]));

        $this->assertCollectionResponse($response, self::FIELDS)->assertJsonCount(2, 'data');
    }

    #[Test]
    #[Depends('it_shows_available_districts')]
    public function it_shows_districts_by_search_query(): void
    {
        $response = $this->getJson($this->path(query: [
            'search' => 'Pekalongan',
        ]));

        $this->assertCollectionResponse($response, self::FIELDS)->assertJsonCount(5, 'data');
    }

    #[Test]
    #[Depends('it_shows_available_districts')]
    #[DataProvider('availableQueries')]
    public function it_shows_single_district(?string ...$with): void
    {
        $response = $this->getJson($this->path('33.75.03', [
            'with' => $with,
        ]));

        $this->assertSingleResponse($response, self::FIELDS, $with);
    }

    #[Test]
    #[Depends('it_shows_available_districts')]
    #[DataProvider('possibleSearchVillages')]
    public function it_shows_available_villages_in_a_district(?string $search): void
    {
        $response = $this->getJson($this->path('33.75.03/villages', [
            'search' => $search,
        ]));

        $this->assertCollectionResponse($response, VillageTest::FIELDS);
    }
}
