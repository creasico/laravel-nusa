<?php

declare(strict_types=1);

namespace Creasi\Tests\Models;

use Creasi\Nusa\Contracts\Address as AddressContract;
use Creasi\Nusa\Models\Address;
use Creasi\Nusa\Models\Village;
use Creasi\Tests\Fixtures\HasManyAddresses;
use Creasi\Tests\Fixtures\HasOneAddress;
use Creasi\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('models')]
#[Group('addresses')]
class AddressTest extends TestCase
{
    use WithFaker;

    #[Test]
    public function it_may_accociate_with_address(): AddressContract
    {
        $village = Village::query()->inRandomOrder()->first();
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
        $addressable = HasManyAddresses::create();

        $addressable->addresses()->save($address);

        $this->assertCount(1, $addressable->addresses);
    }

    #[Test]
    #[Depends('it_may_accociate_with_address')]
    public function may_has_one_address(Address $address): void
    {
        $addressable = HasOneAddress::create();

        $addressable->address()->save($address);

        $this->assertInstanceOf(AddressContract::class, $addressable->address);
    }
}
