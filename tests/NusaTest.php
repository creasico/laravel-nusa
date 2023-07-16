<?php

declare(strict_types=1);

namespace Creasi\Tests;

use Creasi\Nusa\Models\Address;
use Creasi\Nusa\Models\Village;
use Creasi\Tests\Fixtures\HasManyAddresses;
use Creasi\Tests\Fixtures\HasOneAddress;
use Creasi\Tests\Models\ProvinceTest;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\Test;

class NusaTest extends TestCase
{
    use WithFaker;

    /**
     * @param  Collection<int, Village>  $villages
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

        $address->associateWith($village);

        $this->assertSame($village->province, $address->province);
        $this->assertNull($address->owner);

        return $address->fresh();
    }

    #[Test]
    #[Depends('it_may_accociate_with_address')]
    public function may_has_many_addresses(Address $address)
    {
        $owner = new HasManyAddresses();

        $owner->save();

        $owner->addresses()->save($address);

        $this->assertCount(1, $owner->addresses);
    }

    #[Test]
    #[Depends('it_may_accociate_with_address')]
    public function may_has_one_address(Address $address)
    {
        $owner = new HasOneAddress();

        $owner->save();

        $owner->address()->save($address);

        $this->assertInstanceOf(Address::class, $owner->address);
    }
}
