<?php

declare(strict_types=1);

namespace Creasi\Tests\Models;

use Creasi\Nusa\Contracts\Coordinate as CoordinateContracts;
use Creasi\Nusa\Models\Village;
use Creasi\Nusa\Models\Coordinate;
use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;
use Creasi\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('models')]
#[Group('coordinates')]

class CoordinateTest extends TestCase
{
    use WithFaker;

    private function createCoordinates(array $attrs): Coordinate
    {
        return $this->app->make(CoordinateContracts::class)->create($attrs);
    }

    #[Test]
    public function it_may_accociate_with_coordinate(): void
    {
        $coordinate = Coordinate::query()->inRandomOrder()->first();
        $this->assertNotNull($coordinate);
    }
    #[Test]
    public function it_may_accociate_with_province(): void
    {
        $province = Province::query()->inRandomOrder()->first();
        $this->assertNotNull($province);
    }
    #[Test]
    public function it_may_accociate_with_regency(): void
    {
        $regency = Regency::query()->inRandomOrder()->first();
        $this->assertNotNull($regency);
    }
    #[Test]
    public function it_may_accociate_with_district(): void
    {
        $district = District::query()->inRandomOrder()->first();
        $this->assertNotNull($district);
    }
    #[Test]
    public function it_may_accociate_with_village(): void
    {
        $village = Village::query()->inRandomOrder()->first();
        $this->assertNotNull($village);
    }

}




