<?php

declare(strict_types=1);

namespace Creasi\Tests\Models;

use Creasi\Nusa\Contracts\District;
use Creasi\Nusa\Contracts\Province;
use Creasi\Nusa\Contracts\Regency as RegencyContract;
use Creasi\Nusa\Contracts\Village;
use Creasi\Nusa\Models\Regency;
use Creasi\Tests\TestCase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('models')]
#[Group('regencies')]
class RegencyTest extends TestCase
{
    public static function searchProvider(): array
    {
        return [
            'by name' => ['Pekalongan'],
        ];
    }

    #[Test]
    #[DependsOnClass(ProvinceTest::class)]
    #[DataProvider('searchProvider')]
    public function it_should_be_able_to_search(string $keyword): void
    {
        $regency = Regency::search($keyword)->first();

        $this->assertNotNull($regency);
    }

    /**
     * @param  Collection<int, Regency>  $regencies
     * @return Collection<int, Regency>
     */
    #[Test]
    #[DependsExternal(ProvinceTest::class, 'it_should_has_many_regencies')]
    public function it_should_has_many_regencies(Collection $regencies): Collection
    {
        $regencies->each(function (Regency $regency) {
            $this->assertIsString($regency->code, 'Code should be string');
            // Comment this out due to https://github.com/cahyadsn/wilayah/pull/47
            // $this->assertIsFloat($regency->latitude, \sprintf('Latitude of regency "%s" should be float', $regency->code));
            // $this->assertIsFloat($regency->longitude, \sprintf('Longitude of regency "%s" should be float', $regency->code));
            $this->assertInstanceOf(Collection::class, $regency->postal_codes, 'Postal Codes should be instance of collection');

            $this->assertInstanceOf(RegencyContract::class, $regency);
        });

        return $regencies;
    }

    /**
     * @param  Collection<int, Regency>  $regencies
     */
    #[Test]
    #[Depends('it_should_has_many_regencies')]
    public function it_should_belongs_to_province(Collection $regencies): void
    {
        $regencies->each(function (Regency $regency) {
            $this->assertInstanceOf(Province::class, $regency->province);
        });
    }

    /**
     * @param  Collection<int, Regency>  $regencies
     */
    #[Test]
    #[Depends('it_should_has_many_regencies')]
    public function it_should_has_many_districts(Collection $regencies): void
    {
        $regencies->take(5)->each(function (Regency $regency) {
            $this->assertTrue($regency->districts->take(5)->every(fn ($dis) => $dis instanceof District));
        });
    }

    /**
     * @param  Collection<int, Regency>  $regencies
     */
    #[Test]
    #[Depends('it_should_has_many_regencies')]
    public function it_should_has_many_villages(Collection $regencies): void
    {
        $regencies->take(5)->each(function (Regency $regency) {
            $this->assertTrue($regency->villages->take(5)->every(fn ($vil) => $vil instanceof Village));
        });
    }
}
