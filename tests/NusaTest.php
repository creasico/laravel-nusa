<?php

declare(strict_types=1);

namespace Creasi\Tests;

use Creasi\Nusa\Models\Address;
use Creasi\Nusa\Models\Village;
use Creasi\Tests\Models\ProvinceTest;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\Test;

class NusaTest extends TestCase
{
    use WithFaker;

    /**
     * @param Collection<int, Village> $villages
     * @return Collection<int, Village>
     */
    #[Test]
    #[DependsExternal(ProvinceTest::class, 'it_should_has_many_villages')]
    public function it_may_accociate_with_address(Collection $villages)
    {
        $village = $villages->random()->first();

        $address = new Address([
            'line' => $this->faker->streetAddress(),
        ]);

        $address->save();

        $address->province()->associate($village->province);
        $address->regency()->associate($village->regency);
        $address->district()->associate($village->district);
        $address->village()->associate($village);

        $this->assertTrue(true);
    }
}
