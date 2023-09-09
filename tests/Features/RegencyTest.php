<?php

namespace Creasi\Tests\Features;

use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('api')]
#[Group('regencies')]
class RegencyTest extends TestCase
{
    protected $path = 'nusa/regencies';

    protected $fields = ['code', 'name', 'province_code'];

    #[Test]
    #[DependsOnClass(ProvinceTest::class)]
    public function it_shows_all_available_regencies()
    {
        $response = $this->getJson($this->path);

        $response->assertOk()->assertJsonStructure([
            'data' => [$this->fields],
        ]);
    }

    #[Test]
    public function it_shows_regencies_by_selected_codes()
    {
        $response = $this->getJson($this->path(query: [
            'codes' => [3375, 3325],
        ]));

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    #[Test]
    public function it_shows_errors_when_codes_item_is_not_numeric()
    {
        $response = $this->getJson($this->path(query: [
            'codes' => ['foo'],
        ]));

        $response->assertUnprocessable();
    }

    #[Test]
    public function it_shows_errors_when_codes_is_not_an_array()
    {
        $response = $this->getJson($this->path(query: [
            'codes' => 33,
        ]));

        $response->assertUnprocessable();
    }

    #[Test]
    public function it_shows_regencies_by_search_query()
    {
        $response = $this->getJson($this->path(query: [
            'search' => 'Pekalongan',
        ]));

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    #[Test]
    public function it_shows_single_regency()
    {
        $response = $this->getJson($this->path('3375'));

        $response->assertOk()->assertJsonStructure([
            'data' => $this->fields,
        ]);
    }

    #[Test]
    public function it_shows_available_districts_in_a_regency()
    {
        $response = $this->getJson($this->path('3375/districts'));

        $response->assertOk()->assertJsonStructure([
            'data' => [$this->fields],
        ]);
    }

    #[Test]
    public function it_shows_available_villages_in_a_regency()
    {
        $response = $this->getJson($this->path('3375/villages'));

        $response->assertOk()->assertJsonStructure([
            'data' => [$this->fields],
        ]);
    }
}
