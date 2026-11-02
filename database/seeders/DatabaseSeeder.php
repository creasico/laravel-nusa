<?php

declare(strict_types=1);

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

        $path = realpath(\dirname(__DIR__).'/..').'/resources/static';

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

    /**
     * @return array<int|string, mixed>
     */
    private function loadJson(string ...$paths): array
    {
        $path = implode(\DIRECTORY_SEPARATOR, $paths);
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        $data = json_decode($contents, true);

        if (! \is_array($data)) {
            throw new \RuntimeException("Invalid JSON in {$path}.");
        }

        return $data;
    }
}
