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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('models')]
#[Group('districts')]
class DistrictTest extends TestCase
{
    public static function searchProvider(): array
    {
        return [
            'by name' => ['Pekalongan'],
        ];
    }

    #[Test]
    #[DependsOnClass(RegencyTest::class)]
    #[DataProvider('searchProvider')]
    public function it_should_be_able_to_search(string $keyword): void
    {
        $district = District::search($keyword)->first();

        $this->assertNotNull($district);
    }

    /**
     * @param  Collection<int, District>  $districts
     * @return Collection<int, District>
     */
    #[Test]
    #[DependsExternal(ProvinceTest::class, 'it_should_has_many_districts')]
    public function it_should_has_many_districts(Collection $districts): Collection
    {
        $districts->each(function (District $district) {
            $this->assertIsString($district->code, 'Code should be int');
            // $this->assertIsFloat($district->latitude, \sprintf('Latitude of district "%s" should be float', $district->code));
            // $this->assertIsFloat($district->longitude, \sprintf('Longitude of district "%s" should be float', $district->code));
            $this->assertInstanceOf(Collection::class, $district->postal_codes, 'Postal Codes should be instance of collection');

            $this->assertInstanceOf(DistrictContract::class, $district);
        });

        return $districts;
    }

    /**
     * @param  Collection<int, District>  $districts
     */
    #[Test]
    #[Depends('it_should_has_many_districts')]
    public function it_should_belongs_to_province(Collection $districts): void
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
    public function it_should_belongs_to_regency(Collection $regencies): void
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
    public function it_should_has_many_villages(Collection $districts): void
    {
        $districts->each(function (District $district) {
            $this->assertTrue($district->villages->every(fn ($vil) => $vil instanceof Village));
        });
    }
}
