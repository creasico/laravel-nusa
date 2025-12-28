<?php

namespace Creasi\Tests\Features;

use Creasi\Nusa\Models\Village;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('api')]
class ApiTest extends TestCase
{
    private const JSON_FIELDS = [
        'code',
        'name',
        'latitude',
        'longitude',
        // 'coordinates'
    ];

    private const GEOJSON_FIELDS = [
        'type',
        'features' => [
            '*' => [
                'type',
                'properties',
                'geometry' => ['type', 'coordinates'],
            ],
        ],
    ];

    protected $path = 'nusa';

    public static function availableQueries(): array
    {
        return [];
    }

    public static function invalidCodes(): array
    {
        return [];
    }

    #[Test]
    public function it_shows_all_provinces_as_json(): string
    {
        $response = $this->getJson($this->path);

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure(['*' => self::JSON_FIELDS]);

        return collect($response->json())
            ->pluck('code')
            ->random(1)
            ->first();
    }

    #[Test]
    public function it_supports_csv_format_via_accept_header(): void
    {
        $response = $this->get($this->path, [
            'Accept' => 'text/csv',
        ]);

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    #[Test]
    public function it_throws_406_on_unacceptable_index_format(): void
    {
        $response = $this->get($this->path, [
            'Accept' => '*/*',
        ]);

        $response->assertNotAcceptable();
    }

    #[Test]
    public function it_throws_404_on_invalid_province(): void
    {
        $response = $this->getJson($this->path('invalid'));

        $response->assertNotFound()
            ->assertExactJson([
                'message' => 'The route nusa/invalid could not be found.',
            ]);
    }

    #[Test]
    #[Depends('it_shows_all_provinces_as_json')]
    public function it_shows_all_regencies_in_a_province_as_json(string $province): string
    {
        $response = $this->getJson($this->path($province));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                ...self::JSON_FIELDS,
                'regencies' => ['*' => self::JSON_FIELDS],
            ]);

        $province = $response->json();

        return str_replace(
            '.',
            '/',
            collect($province['regencies'])
                ->pluck('code')
                ->random(1)
                ->first()
        );
    }

    #[Test]
    #[Depends('it_shows_all_provinces_as_json')]
    public function it_shows_all_regencies_in_a_province_as_csv(string $province): void
    {
        $response = $this->get($this->path($province), [
            'Accept' => 'text/csv',
        ]);

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    #[Test]
    #[Depends('it_shows_all_provinces_as_json')]
    public function it_supports_geojson_format_of_province(string $province): void
    {
        $response = $this->get($this->path($province), [
            'Accept' => 'application/geo+json',
        ]);

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/geo+json; charset=UTF-8')
            ->assertJsonStructure(self::GEOJSON_FIELDS);
    }

    #[Test]
    #[Depends('it_shows_all_provinces_as_json')]
    public function it_throws_406_on_unacceptable_province_format(string $province): void
    {
        $response = $this->get($this->path($province), [
            'Accept' => '*/*',
        ]);

        $response->assertNotAcceptable();
    }

    #[Test]
    public function it_throws_404_on_invalid_regency(): void
    {
        $response = $this->getJson($this->path('invalid/invalid'));

        $response->assertNotFound()
            ->assertExactJson([
                'message' => 'The route nusa/invalid/invalid could not be found.',
            ]);
    }

    #[Test]
    #[Depends('it_shows_all_regencies_in_a_province_as_json')]
    public function it_shows_all_districts_in_a_regency_as_json(string $regency): string
    {
        $response = $this->getJson($this->path($regency));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                ...self::JSON_FIELDS,
                'districts' => ['*' => self::JSON_FIELDS],
            ]);

        $regency = $response->json();

        return str_replace(
            '.',
            '/',
            collect($regency['districts'])
                ->pluck('code')
                ->random(1)
                ->first()
        );
    }

    #[Test]
    #[Depends('it_shows_all_regencies_in_a_province_as_json')]
    public function it_shows_all_districts_in_a_regency_as_csv(string $regency): void
    {
        $response = $this->get($this->path($regency), [
            'Accept' => 'text/csv',
        ]);

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    #[Test]
    #[Depends('it_shows_all_regencies_in_a_province_as_json')]
    public function it_supports_geojson_format_of_regency(string $regency): void
    {
        $response = $this->get($this->path($regency), [
            'Accept' => 'application/geo+json',
        ]);

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/geo+json; charset=UTF-8')
            ->assertJsonStructure(self::GEOJSON_FIELDS);
    }

    #[Test]
    #[Depends('it_shows_all_regencies_in_a_province_as_json')]
    public function it_throws_406_on_unacceptable_regency_format(string $regency): void
    {
        $response = $this->get($this->path($regency), [
            'Accept' => '*/*',
        ]);

        $response->assertNotAcceptable();
    }

    #[Test]
    public function it_throws_404_on_invalid_district(): void
    {
        $response = $this->getJson($this->path('invalid/invalid/invalid'));

        $response->assertNotFound()
            ->assertExactJson([
                'message' => 'The route nusa/invalid/invalid/invalid could not be found.',
            ]);
    }

    #[Test]
    #[Depends('it_shows_all_districts_in_a_regency_as_json')]
    public function it_shows_all_villages_in_a_district_as_json(string $district): string
    {
        $response = $this->getJson($this->path($district));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                ...self::JSON_FIELDS,
                'villages' => ['*' => self::JSON_FIELDS],
            ]);

        $district = $response->json();

        return str_replace(
            '.',
            '/',
            collect($district['villages'])
                ->pluck('code')
                ->random(1)
                ->first()
        );
    }

    #[Test]
    #[Depends('it_shows_all_districts_in_a_regency_as_json')]
    public function it_shows_all_villages_in_a_district_as_csv(string $district): void
    {
        $response = $this->get($this->path($district), [
            'Accept' => 'text/csv',
        ]);

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    #[Test]
    #[Depends('it_shows_all_districts_in_a_regency_as_json')]
    public function it_supports_geojson_format_of_district(string $district): void
    {
        $response = $this->get($this->path($district), [
            'Accept' => 'application/geo+json',
        ]);

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/geo+json; charset=UTF-8')
            ->assertJsonStructure(self::GEOJSON_FIELDS);
    }

    #[Test]
    #[Depends('it_shows_all_districts_in_a_regency_as_json')]
    public function it_throws_406_on_unacceptable_district_format(string $district): void
    {
        $response = $this->get($this->path($district), [
            'Accept' => '*/*',
        ]);

        $response->assertNotAcceptable();
    }

    #[Test]
    public function it_throws_404_on_invalid_village(): void
    {
        $response = $this->getJson($this->path('invalid/invalid/invalid/invalid'));

        $response->assertNotFound()
            ->assertExactJson([
                'message' => 'The route nusa/invalid/invalid/invalid/invalid could not be found.',
            ]);
    }

    #[Test]
    #[Depends('it_shows_all_villages_in_a_district_as_json')]
    public function it_shows_a_village_as_json(string $village): void
    {
        $response = $this->getJson($this->path($village));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                ...self::JSON_FIELDS,
                'postal_code',
            ]);
    }

    #[Test]
    #[Depends('it_shows_all_villages_in_a_district_as_json')]
    public function it_supports_geojson_format_of_village(string $village): void
    {
        $response = $this->get($this->path($village), [
            'Accept' => 'application/geo+json',
        ]);

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/geo+json; charset=UTF-8')
            ->assertJsonStructure(self::GEOJSON_FIELDS);
    }

    #[Test]
    #[Depends('it_shows_all_villages_in_a_district_as_json')]
    public function it_throws_406_on_unacceptable_village_format(string $village): void
    {
        $response = $this->get($this->path($village), [
            'Accept' => '*/*',
        ]);

        $response->assertNotAcceptable();
    }

    #[Test]
    public function it_throws_404_on_unavailable_geojson_village(): void
    {
        $village = Village::query()->whereNull('coordinates')->take(1)->get('code')->first();

        if (! $village) {
            $this->markTestSkipped('No village without coordinates found.');
        }

        $path = str_replace('.', '/', $village->code);

        $response = $this->get($this->path($path), [
            'Accept' => 'application/geo+json',
        ]);

        $response->assertNotFound()
            ->assertExactJson([
                'message' => "The geojson for {$village->code} could not be found.",
            ]);
    }
}
