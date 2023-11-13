<?php

namespace Database\Seeders;

use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Village;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (Province::query()->count() > 0) {
            return;
        }

        $path = \realpath(\dirname(__DIR__).'/..').'/resources/json';

        foreach ($this->loadJson($path, 'provinces.json') as $province) {
            /** @var Province */
            $province = Province::query()->create($province);

            $province->regencies()->createMany(
                $this->loadJson($path, $province->code, 'regencies.json')
            );

            District::query()->insert(
                $this->loadJson($path, $province->code, 'districts.json')
            );

            Village::query()->insert(
                $this->loadJson($path, $province->code, 'villages.json')
            );
        }
    }

    private function loadJson(string ...$paths): array
    {
        $path = \implode(\DIRECTORY_SEPARATOR, $paths);

        return \json_decode(file_get_contents($path), true);
    }
}
