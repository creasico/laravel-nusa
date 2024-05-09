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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('models')]
#[Group('addresses')]
class AddressTest extends TestCase
{
    use WithFaker;

    private function createAddress(array $attrs): Address
    {
        return $this->app->make(AddressContract::class)->create($attrs);
    }

    #[Test]
    public function it_may_accociate_with_address(): void
    {
        $village = Village::query()->inRandomOrder()->first();
        $address = $this->createAddress([
            'line' => $this->faker->streetAddress(),
        ]);

        $address->associateWith($village);

        $this->assertSame($village->province, $address->province);
        $this->assertNull($address->addressable);
    }

    #[Test]
    public function may_has_many_addresses(): void
    {
        /** @var HasManyAddresses */
        $addressable = HasManyAddresses::create();

        $addressable->addresses()->save(
            $this->createAddress(['line' => 'Coba Alamat'])
        );

        $this->assertCount(1, $addressable->addresses);
    }

    #[Test]
    public function may_has_one_address(): void
    {
        /** @var HasOneAddress */
        $addressable = HasOneAddress::create();

        $addressable->address()->save(
            $this->createAddress(['line' => 'Coba Alamat'])
        );

        $this->assertInstanceOf(AddressContract::class, $addressable->address);
    }
}
