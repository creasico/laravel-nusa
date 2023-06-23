<?php

declare(strict_types=1);

namespace Creasi\Tests\Models;

use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;
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
    #[Test]
    #[DependsExternal(DistrictTest::class, 'it_should_be_true')]
    public function it_should_be_true()
    {
        $this->assertTrue(\class_exists(Village::class));

        return Village::with('province', 'regency', 'district')->take(10)->get();
    }

    #[Test]
    #[Depends('it_should_be_true')]
    public function it_should_belongs_to_province(Collection $villages)
    {
        $villages->each(function (Village $vil) {
            $this->assertInstanceOf(Province::class, $vil->province);
        });
    }

    #[Test]
    #[Depends('it_should_be_true')]
    public function it_should_belongs_to_regency(Collection $villages)
    {
        $villages->each(function (Village $vil) {
            $this->assertInstanceOf(Regency::class, $vil->regency);
        });
    }

    #[Test]
    #[Depends('it_should_be_true')]
    public function it_should_belongs_to_district(Collection $villages)
    {
        $villages->each(function (Village $vil) {
            $this->assertInstanceOf(District::class, $vil->district);
        });
    }
}
