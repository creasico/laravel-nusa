<?php

namespace Creasi\Tests\Features;

use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('api')]
#[Group('villages')]
class VillageTest extends TestCase
{
    protected $path = 'nusa/villages';

    protected $fields = [
        'code',
        'name',
        'district_code',
        'regency_code',
        'province_code',
        'postal_code',
    ];

    #[Test]
    #[DependsOnClass(DistrictTest::class)]
    public function it_shows_available_villages()
    {
        $response = $this->getJson($this->path);

        $response->assertOk()->assertJsonStructure([
            'data' => [$this->fields],
        ]);
    }

    #[Test]
    public function it_shows_single_village()
    {
        $response = $this->getJson($this->path('3375031006'));

        $response->assertOk()->assertJsonStructure([
            'data' => $this->fields,
        ]);
    }
}
