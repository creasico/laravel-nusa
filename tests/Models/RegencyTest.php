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
#[Group('regencies')]
class RegencyTest extends TestCase
{
    #[Test]
    #[DependsExternal(ProvinceTest::class, 'it_should_be_true')]
    public function it_should_be_true()
    {
        $this->assertTrue(\class_exists(Regency::class));

        return Regency::with([
            'province',
            'districts' => function ($query) {
                $query->take(10);
            },
            'villages' => function ($query) {
                $query->take(10);
            }
        ])->take(10)->get();
    }

    #[Test]
    #[Depends('it_should_be_true')]
    public function it_should_belongs_to_province(Collection $regencies)
    {
        $regencies->each(function (Regency $reg) {
            $this->assertInstanceOf(Province::class, $reg->province);
        });
    }

    #[Test]
    #[Depends('it_should_be_true')]
    public function it_should_has_many_districts(Collection $regencies)
    {
        $regencies->each(function (Regency $reg) {
            $this->assertTrue($reg->districts->every(fn ($dis) => $dis instanceof District));
        });
    }

    #[Test]
    #[Depends('it_should_be_true')]
    public function it_should_has_many_villages(Collection $regencies)
    {
        $regencies->each(function (Regency $reg) {
            $this->assertTrue($reg->villages->every(fn ($vil) => $vil instanceof Village));
        });
    }
}
