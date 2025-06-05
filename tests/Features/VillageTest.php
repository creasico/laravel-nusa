<?php

namespace Creasi\Tests\Features;

use Creasi\Tests\Models\VillageTest as ModelTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('api')]
#[Group('villages')]
class VillageTest extends TestCase
{
    public const FIELDS = [
        'code',
        'name',
        'district_code',
        'regency_code',
        'province_code',
        'postal_code',
        'latitude',
        'longitude',
    ];

    protected $path = 'nusa/villages';

    public static function availableQueries(): array
    {
        return [
            'basic request' => [],
            'include province' => ['province'],
            'include regency' => ['regency'],
            'include district' => ['district'],
        ];
    }

    public static function invalidCodes(): array
    {
        return [
            // 'array of non-numeric code' => [['foo']],
            'non-array of numeric code' => ['33.75.03.1004'],
        ];
    }

    #[Test]
    #[DependsOnClass(ModelTest::class)]
    #[DependsOnClass(DistrictTest::class)]
    #[DataProvider('availableQueries')]
    public function it_shows_available_villages(?string ...$with): void
    {
        $response = $this->getJson($this->path(query: [
            'with' => $with,
        ]));

        $this->assertCollectionResponse($response, self::FIELDS, $with);
    }

    #[Test]
    #[Depends('it_shows_available_villages')]
    #[DataProvider('invalidCodes')]
    public function it_shows_errors_for_invalid_codes(mixed $codes): void
    {
        $response = $this->getJson($this->path(query: [
            'codes' => $codes,
        ]));

        $response->assertUnprocessable();
    }

    #[Test]
    #[Depends('it_shows_available_villages')]
    public function it_shows_villages_by_selected_codes(): void
    {
        $response = $this->getJson($this->path(query: [
            'codes' => ['33.75.03.1004', '33.75.03.1006'],
        ]));

        $this->assertCollectionResponse($response, self::FIELDS)->assertJsonCount(2, 'data');
    }

    #[Test]
    #[Depends('it_shows_available_villages')]
    public function it_shows_villages_by_postal_codes(): void
    {
        $response = $this->getJson($this->path(query: [
            'postal_code' => 51252,
        ]));

        $this->assertCollectionResponse($response, self::FIELDS)->assertJsonCount(15, 'data');
    }

    #[Test]
    #[Depends('it_shows_available_villages')]
    public function it_shows_villages_by_search_query(): void
    {
        $response = $this->getJson($this->path(query: [
            'search' => 'Padukuhan Kraton',
        ]));

        $this->assertCollectionResponse($response, self::FIELDS)->assertJsonCount(1, 'data');
    }

    #[Test]
    #[Depends('it_shows_available_villages')]
    #[DataProvider('availableQueries')]
    public function it_shows_single_village(?string ...$with): void
    {
        $response = $this->getJson($this->path('33.75.03.1006', [
            'with' => $with,
        ]));

        $this->assertSingleResponse($response, self::FIELDS, $with);
    }
}
