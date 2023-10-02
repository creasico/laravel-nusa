<?php

declare(strict_types=1);

namespace Creasi\Tests\Models;

use Creasi\Nusa\Contracts\Address as AddressContract;
use Creasi\Nusa\Models\Address;
use Creasi\Tests\Fixtures\HasManyAddresses;
use Creasi\Tests\Fixtures\HasOneAddress;
use Creasi\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('models')]
#[Group('addresses')]
class AddressTest extends TestCase
{
    use WithFaker;

    /**
     * @param  Collection<int, Village>  $villages
     */
    #[Test]
    #[DependsExternal(VillageTest::class, 'it_should_has_many_villages')]
    public function it_may_accociate_with_address(Collection $villages): AddressContract
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
    public function may_has_many_addresses(Address $address): void
    {
        $addressable = new HasManyAddresses();

        $addressable->save();

        $addressable->addresses()->save($address);

        $this->assertCount(1, $addressable->addresses);
    }

    #[Test]
    #[Depends('it_may_accociate_with_address')]
    public function may_has_one_address(Address $address): void
    {
        $addressable = new HasOneAddress();

        $addressable->save();

        $addressable->address()->save($address);

        $this->assertInstanceOf(AddressContract::class, $addressable->address);
    }
}
