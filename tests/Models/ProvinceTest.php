<?php

declare(strict_types=1);

namespace Creasi\Tests\Models;

use Creasi\Nusa\Contracts\District;
use Creasi\Nusa\Contracts\Province as ProvinceContract;
use Creasi\Nusa\Contracts\Regency;
use Creasi\Nusa\Contracts\Village;
use Creasi\Nusa\Models\Province;
use Creasi\Tests\TestCase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('models')]
#[Group('provinces')]
class ProvinceTest extends TestCase
{
    public static function searchProvider()
    {
        return [
            'by code' => [33],
            'by name' => ['Jawa Tengah'],
        ];
    }

    #[Test]
    #[DataProvider('searchProvider')]
    public function it_should_be_able_to_search(string|int $keyword)
    {
        $province = Province::search($keyword)->first();

        $this->assertNotNull($province);
    }

    #[Test]
    public function it_should_be_true()
    {
        if (! env('GIT_BRANCH')) {
            $this->artisan('nusa:sync');
        }

        $provinces = Province::with([
            'regencies' => function ($query) {
                $query->take(10);
            },
            'districts' => function ($query) {
                $query->take(10);
            },
            'villages' => function ($query) {
                $query->take(10);
            },
        ])->get();

        $provinces->each(function (Province $province) {
            $this->assertIsInt($province->code, 'Code should be int');
            $this->assertIsFloat($province->latitude, 'Latitude should be float');
            $this->assertIsFloat($province->longitude, 'Longitude should be float');
            $this->assertIsArray($province->coordinates, 'Coordinates should be array');
            $this->assertInstanceOf(Collection::class, $province->postal_codes, 'Postal Codes should be instance of collection');

            $this->assertInstanceOf(ProvinceContract::class, $province);
        });

        return $provinces;
    }

    /**
     * @param  Collection<int, Province>  $provinces
     */
    #[Test]
    #[Depends('it_should_be_true')]
    public function it_should_has_many_regencies(Collection $provinces)
    {
        $regencies = collect();

        foreach ($provinces as $province) {
            $this->assertTrue($province->regencies->every(fn ($reg) => $reg instanceof Regency));

            $regencies->push(...$province->regencies);
        }

        return $regencies;
    }

    /**
     * @param  Collection<int, Province>  $provinces
     */
    #[Test]
    #[Depends('it_should_be_true')]
    public function it_should_has_many_districts(Collection $provinces)
    {
        $districts = \collect();

        foreach ($provinces as $province) {
            $this->assertTrue($province->districts->every(fn ($dis) => $dis instanceof District));

            $districts->push(...$province->districts);
        }

        return $districts;
    }

    /**
     * @param  Collection<int, Province>  $provinces
     */
    #[Test]
    #[Depends('it_should_be_true')]
    public function it_should_has_many_villages(Collection $provinces)
    {
        $villages = \collect();

        foreach ($provinces as $province) {
            $this->assertTrue($province->villages->every(fn ($vil) => $vil instanceof Village));

            $villages->push(...$province->villages);
        }

        return $villages;
    }
}
