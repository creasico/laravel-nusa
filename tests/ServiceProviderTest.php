<?php

declare(strict_types=1);

namespace Creasi\Tests;

use Creasi\Nusa\Contracts\Address as AddressContract;
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

class ServiceProviderTest extends TestCase
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

        $address = $this->app->make(AddressContract::class)->create([
            'line' => $this->faker->streetAddress(),
        ]);

        $address->associateWith($village);

        $this->assertSame($village->province, $address->province);
        $this->assertNull($address->addressable);

        return $address->fresh();
    }

    #[Test]
    #[Depends('it_may_accociate_with_address')]
    public function may_has_many_addresses(Address $address)
    {
        $addressable = new HasManyAddresses();

        $addressable->save();

        $addressable->addresses()->save($address);

        $this->assertCount(1, $addressable->addresses);
    }

    #[Test]
    #[Depends('it_may_accociate_with_address')]
    public function may_has_one_address(Address $address)
    {
        $addressable = new HasOneAddress();

        $addressable->save();

        $addressable->address()->save($address);

        $this->assertInstanceOf(AddressContract::class, $addressable->address);
    }
}
