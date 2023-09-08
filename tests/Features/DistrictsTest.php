<?php

namespace Creasi\Tests\Features;

use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('api')]
#[Group('districts')]
class DistrictsTest extends TestCase
{
    private $path = 'nusa/districts';

    protected $fields = ['code', 'name', 'regency_code', 'province_code'];

    #[Test]
    #[DependsOnClass(RegenciesTest::class)]
    public function it_shows_available_districts()
    {
        $response = $this->getJson($this->path);

        $response->assertOk()->assertJsonStructure([
            'data' => [$this->fields],
        ]);
    }

    #[Test]
    public function it_shows_single_district()
    {
        $response = $this->getJson($this->path.'/337503');

        $response->assertOk()->assertJsonStructure([
            'data' => $this->fields,
        ]);
    }

    #[Test]
    public function it_shows_available_villages_in_a_district()
    {
        $response = $this->getJson($this->path.'/337503/villages');

        $response->assertOk()->assertJsonStructure([
            'data' => [$this->fields],
        ]);
    }
}
