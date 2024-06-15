<?php

declare(strict_types=1);

namespace Creasi\Tests\Models;

use Creasi\Nusa\Contracts\HasCoordinate;
use Creasi\Nusa\Contracts\LatitudeLongitude as LatitudeLongitudeContract;
use Creasi\Nusa\Contracts\Village;
use Creasi\Nusa\Models\Coordinate;
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
        return $this->app->make(HasCoordinate::class)->create($attrs);
    }

    #[Test]
    public function it_may_accociate_with_coordinate(): void
    {

    }
}




