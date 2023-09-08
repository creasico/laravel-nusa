<?php

namespace Creasi\Tests\Features;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('api')]
#[Group('provinces')]
class ProvincesTest extends TestCase
{
    protected $path = 'nusa/provinces';

    protected $fields = ['code', 'name', 'latitude', 'longitude', 'coordinates', 'postal_codes'];

    #[Test]
    public function it_shows_all_available_provinces()
    {
        $response = $this->getJson($this->path);

        $response->assertOk()->assertJsonStructure([
            'data' => [$this->fields],
        ]);
    }

    #[Test]
    public function it_shows_provinces_by_search_query()
    {
        $response = $this->getJson($this->path(query: ['search' => 'Jawa Tengah']));

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    #[Test]
    public function it_shows_single_province()
    {
        $response = $this->getJson($this->path('33'));

        $response->assertOk()->assertJsonStructure([
            'data' => $this->fields,
        ]);
    }

    #[Test]
    public function it_shows_available_regencies_in_a_province()
    {
        $response = $this->getJson($this->path('33/regencies'));

        $response->assertOk();
    }

    #[Test]
    public function it_shows_available_districts_in_a_province()
    {
        $response = $this->getJson($this->path('33/districts'));

        $response->assertOk();
    }

    #[Test]
    public function it_shows_available_villages_province()
    {
        $response = $this->getJson($this->path('33/villages'));

        $response->assertOk();
    }
}
