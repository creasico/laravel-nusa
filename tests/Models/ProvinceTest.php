<?php

declare(strict_types=1);

namespace Creasi\Tests\Models;

use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;
use Creasi\Nusa\Models\Village;
use Creasi\Tests\NusaTest;
use Creasi\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('models')]
#[Group('provinces')]
class ProvinceTest extends TestCase
{
    #[Test]
    #[DependsExternal(NusaTest::class, 'it_should_be_true')]
    public function it_should_be_true()
    {
        $this->assertTrue(\class_exists(Province::class));

        return Province::with([
            'regencies' => function ($query) {
                $query->take(10);
            },
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
    public function it_should_has_many_regencies(Collection $provinces)
    {
        $provinces->each(function (Province $prov) {
            $this->assertTrue($prov->regencies->every(fn ($reg) => $reg instanceof Regency));
        });
    }

    #[Test]
    #[Depends('it_should_be_true')]
    public function it_should_has_many_districts(Collection $provinces)
    {
        $provinces->each(function (Province $prov) {
            $this->assertTrue($prov->districts->every(fn ($dis) => $dis instanceof District));
        });
    }

    #[Test]
    #[Depends('it_should_be_true')]
    public function it_should_has_many_villages(Collection $provinces)
    {
        $provinces->each(function (Province $prov) {
            $this->assertTrue($prov->villages->every(fn ($vil) => $vil instanceof Village));
        });
    }
}
