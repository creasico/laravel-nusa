<?php

declare(strict_types=1);

namespace Creasi\Tests;

use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;
use Creasi\Nusa\Models\Village;
use Illuminate\Database\Seeder;

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

            $model::insert(\json_decode($content, true));
        }
    }
}
