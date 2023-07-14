<?php

declare(strict_types=1);

namespace Creasi\Tests\Models;

use Creasi\Nusa\Contracts\District as DistrictContract;
use Creasi\Nusa\Contracts\Province;
use Creasi\Nusa\Contracts\Regency;
use Creasi\Nusa\Contracts\Village;
use Creasi\Nusa\Models\District;
use Creasi\Tests\TestCase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('models')]
#[Group('districts')]
class DistrictTest extends TestCase
{
    /**
     * @param  Collection<int, District>  $districts
     * @return Collection<int, District>
     */
    #[Test]
    #[DependsExternal(ProvinceTest::class, 'it_should_has_many_districts')]
    public function it_should_has_many_districts(Collection $districts)
    {
        $districts->each(function (District $district) {
            $this->assertIsInt($district->code, 'Code should be int');

            $this->assertInstanceOf(DistrictContract::class, $district);
        });

        return $districts;
    }

    /**
     * @param  Collection<int, District>  $districts
     */
    #[Test]
    #[Depends('it_should_has_many_districts')]
    public function it_should_belongs_to_province(Collection $districts)
    {
        $districts->each(function (District $district) {
            $this->assertInstanceOf(Province::class, $district->province);
        });
    }

    /**
     * @param  Collection<int, District>  $districts
     */
    #[Test]
    #[Depends('it_should_has_many_districts')]
    public function it_should_belongs_to_regency(Collection $regencies)
    {
        $regencies->each(function (District $district) {
            $this->assertInstanceOf(Regency::class, $district->regency);
        });
    }

    /**
     * @param  Collection<int, District>  $districts
     */
    #[Test]
    #[Depends('it_should_has_many_districts')]
    public function it_should_has_many_villages(Collection $districts)
    {
        $districts->each(function (District $district) {
            $this->assertTrue($district->villages->every(fn ($vil) => $vil instanceof Village));
        });
    }
}
