<?php

namespace Creasi\Tests\Features;

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

    public static function invalidCodes(): array
    {
        return [
            'array of non-numeric code' => [['foo']],
            'non-array of numeric code' => [3375],
        ];
    }

    #[Test]
    #[DependsOnClass(ProvinceTest::class)]
    #[DataProvider('availableQueries')]
    public function it_shows_all_available_regencies(?string ...$with)
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
    #[Depends('it_shows_all_available_regencies')]
    public function it_shows_regencies_by_selected_codes()
    {
        $response = $this->getJson($this->path(query: [
            'codes' => [3375, 3325],
        ]));

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    #[Test]
    #[Depends('it_shows_all_available_regencies')]
    #[DataProvider('invalidCodes')]
    public function it_shows_errors_for_invalid_codes(mixed $codes)
    {
        $response = $this->getJson($this->path(query: [
            'codes' => $codes,
        ]));

        $response->assertUnprocessable();
    }

    #[Test]
    #[Depends('it_shows_all_available_regencies')]
    public function it_shows_regencies_by_search_query()
    {
        $response = $this->getJson($this->path(query: [
            'search' => 'Pekalongan',
        ]));

        $response->assertOk()->assertJsonStructure([
            'data' => [self::FIELDS],
            'meta' => [],
        ])->assertJsonCount(2, 'data');
    }

    #[Test]
    #[Depends('it_shows_all_available_regencies')]
    #[DataProvider('availableQueries')]
    public function it_shows_single_regency(?string ...$with)
    {
        $response = $this->getJson($this->path('3375', [
            'with' => $with,
        ]));

        $response->assertOk()->assertJsonStructure([
            'data' => self::FIELDS,
            'meta' => [],
        ]);
    }

    #[Test]
    #[Depends('it_shows_all_available_regencies')]
    public function it_shows_available_districts_in_a_regency()
    {
        $response = $this->getJson($this->path('3375/districts'));

        $response->assertOk()->assertJsonStructure([
            'data' => [DistrictTest::FIELDS],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'],
        ]);
    }

    #[Test]
    #[Depends('it_shows_all_available_regencies')]
    public function it_shows_available_villages_in_a_regency()
    {
        $response = $this->getJson($this->path('3375/villages'));

        $response->assertOk()->assertJsonStructure([
            'data' => [VillageTest::FIELDS],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'],
        ]);
    }
}
