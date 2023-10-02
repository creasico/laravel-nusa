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
            $this->assertIsInt($regency->code, 'Code should be int');
            $this->assertIsFloat($regency->latitude, 'Latitude should be float');
            $this->assertIsFloat($regency->longitude, 'Longitude should be float');
            $this->assertIsArray($regency->coordinates, 'Coordinates should be array');
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
        $regencies->each(function (Regency $regency) {
            $this->assertTrue($regency->districts->every(fn ($dis) => $dis instanceof District));
        });
    }

    /**
     * @param  Collection<int, Regency>  $regencies
     */
    #[Test]
    #[Depends('it_should_has_many_regencies')]
    public function it_should_has_many_villages(Collection $regencies): void
    {
        $regencies->each(function (Regency $regency) {
            $this->assertTrue($regency->villages->every(fn ($vil) => $vil instanceof Village));
        });
    }
}
