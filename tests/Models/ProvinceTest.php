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
    public static function searchProvider(): array
    {
        return [
            'by name' => ['Jawa'],
        ];
    }

    #[Test]
    #[DataProvider('searchProvider')]
    public function it_should_be_able_to_search(string $keyword): void
    {
        $province = Province::search($keyword)->first();

        $this->assertNotNull($province);
    }

    /**
     * @return Collection<int, Province>
     */
    #[Test]
    public function it_should_be_true(): Collection
    {
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
            $this->assertInstanceOf(Collection::class, $province->postal_codes, 'Postal Codes should be instance of collection');

            if ($province->coordinates) {
                $this->assertIsArray($province->coordinates, 'Coordinates should be array');
            }

            $this->assertInstanceOf(ProvinceContract::class, $province);
        });

        return $provinces;
    }

    /**
     * @param  Collection<int, Province>  $provinces
     * @return Collection<int, Province>
     */
    #[Test]
    #[Depends('it_should_be_true')]
    public function it_should_has_many_regencies(Collection $provinces): Collection
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
     * @return Collection<int, Province>
     */
    #[Test]
    #[Depends('it_should_be_true')]
    public function it_should_has_many_districts(Collection $provinces): Collection
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
     * @return Collection<int, Province>
     */
    #[Test]
    #[Depends('it_should_be_true')]
    public function it_should_has_many_villages(Collection $provinces): Collection
    {
        $villages = \collect();

        foreach ($provinces as $province) {
            $this->assertTrue($province->villages->every(fn ($vil) => $vil instanceof Village));

            $villages->push(...$province->villages);
        }

        return $villages;
    }
}
