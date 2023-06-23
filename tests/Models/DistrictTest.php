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
#[Group('districts')]
class DistrictTest extends TestCase
{
    #[Test]
    #[DependsExternal(RegencyTest::class, 'it_should_be_true')]
    public function it_should_be_true()
    {
        $this->assertTrue(\class_exists(District::class));

        return District::with('province', 'regency', 'villages')->take(10)->get();
    }

    #[Test]
    #[Depends('it_should_be_true')]
    public function it_should_belongs_to_province(Collection $districts)
    {
        $districts->each(function (District $dis) {
            $this->assertInstanceOf(Province::class, $dis->province);
        });
    }

    #[Test]
    #[Depends('it_should_be_true')]
    public function it_should_belongs_to_regency(Collection $regencies)
    {
        $regencies->each(function (District $dis) {
            $this->assertInstanceOf(Regency::class, $dis->regency);
        });
    }

    #[Test]
    #[Depends('it_should_be_true')]
    public function it_should_has_many_villages(Collection $districts)
    {
        $districts->each(function (District $dis) {
            $this->assertTrue($dis->villages->every(fn ($vil) => $vil instanceof Village));
        });
    }
}
