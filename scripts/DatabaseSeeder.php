<?php

declare(strict_types=1);

namespace Creasi\Scripts;

use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;
use Creasi\Nusa\Models\Village;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DatabaseSeeder extends Seeder
{
    private $fileNames = [
        'provinces' => Province::class,
        'regencies' => Regency::class,
        'districts' => District::class,
        'villages' => Village::class,
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ($this->fileNames as $table => $model) {
            $content = file_get_contents(dirname(__DIR__).'/database/json/'.$table.'.json');

            if ($table !== 'villages') {
                $model::insert(\json_decode($content, true));
                continue;
            }

            \collect(\json_decode($content, true))->groupBy('district_code')->each(function (Collection $chunk) use ($model) {
                $model::insert($chunk->take(10)->toArray());
            });
        }
    }
}
