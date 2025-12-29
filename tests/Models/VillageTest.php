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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('models')]
#[Group('villages')]
class VillageTest extends TestCase
{
    public static function searchProvider(): array
    {
        return [
            'by name' => ['Kraton'],
        ];
    }

    #[Test]
    #[DependsOnClass(DistrictTest::class)]
    #[DataProvider('searchProvider')]
    public function it_should_be_able_to_search(string $keyword): void
    {
        $village = Village::search($keyword)->first();

        $this->assertNotNull($village);
    }

    /**
     * @param  Collection<int, Village>  $villages
     * @return Collection<int, Village>
     */
    #[Test]
    #[DependsExternal(ProvinceTest::class, 'it_should_has_many_villages')]
    public function it_should_has_many_villages(Collection $villages): Collection
    {
        $villages->each(function (Village $village) {
            $this->assertIsString($village->code, 'Code should be string');
            $this->assertIsFloat($village->latitude, \sprintf('Latitude of village "%s" should be float', $village->code));
            $this->assertIsFloat($village->longitude, \sprintf('Longitude of village "%s" should be float', $village->code));
            if ($village->postal_code) {
                $this->assertIsInt($village->postal_code, 'Postal Code should be int');
            }

            $this->assertInstanceOf(VillageContract::class, $village);
        });

        return $villages;
    }

    /**
     * @param  Collection<int, Village>  $villages
     */
    #[Test]
    #[Depends('it_should_has_many_villages')]
    public function it_should_belongs_to_province(Collection $villages): void
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
    public function it_should_belongs_to_regency(Collection $villages): void
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
    public function it_should_belongs_to_district(Collection $villages): void
    {
        $villages->each(function (Village $village) {
            $this->assertInstanceOf(District::class, $village->district);
        });
    }
}
