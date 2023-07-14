<?php

declare(strict_types=1);

namespace Creasi\Tests\Models;

use Creasi\Nusa\Contracts\District;
use Creasi\Nusa\Contracts\Province;
use Creasi\Nusa\Contracts\Regency;
use Creasi\Nusa\Contracts\Village as VillageContract;
use Creasi\Nusa\Models\Village;
use Creasi\Tests\TestCase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('models')]
#[Group('villages')]
class VillageTest extends TestCase
{
    /**
     * @param  Collection<int, Village>  $villages
     * @return Collection<int, Village>
     */
    #[Test]
    #[DependsExternal(ProvinceTest::class, 'it_should_has_many_villages')]
    public function it_should_has_many_villages(Collection $villages)
    {
        $villages->each(function (Village $village) {
            $this->assertIsInt($village->code, 'Code should be int');
            $this->assertIsInt($village->postal_code, 'Postal Code should be int');

            $this->assertInstanceOf(VillageContract::class, $village);
        });

        return $villages;
    }

    /**
     * @param  Collection<int, Village>  $villages
     */
    #[Test]
    #[Depends('it_should_has_many_villages')]
    public function it_should_belongs_to_province(Collection $villages)
    {
        $villages->each(function (Village $village) {
            $this->assertInstanceOf(Province::class, $village->province);
        });
    }

    /**
     * @param  Collection<int, Village>  $villages
     */
    #[Test]
    #[Depends('it_should_has_many_villages')]
    public function it_should_belongs_to_regency(Collection $villages)
    {
        $villages->each(function (Village $village) {
            $this->assertInstanceOf(Regency::class, $village->regency);
        });
    }

    /**
     * @param  Collection<int, Village>  $villages
     */
    #[Test]
    #[Depends('it_should_has_many_villages')]
    public function it_should_belongs_to_district(Collection $villages)
    {
        $villages->each(function (Village $village) {
            $this->assertInstanceOf(District::class, $village->district);
        });
    }
}
