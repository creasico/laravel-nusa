<?php

declare(strict_types=1);

namespace Creasi\Nusa\Console;

use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;
use Creasi\Nusa\Models\Village;
use Creasi\Nusa\Normalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use PDO;

class SyncCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'nusa:sync';

    /**
     * @var string
     */
    protected $description = 'Sync database';

    private ?string $libPath = null;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->libPath = \realpath(\dirname(__DIR__).'/..');

        $this->migrateIfNotMigrated();

        $path = "{$this->libPath}/resources/json";

        $provinces = \json_decode(
            file_get_contents("{$path}/provinces.json"),
            true,
        );

        foreach ($provinces as $province) {
            /** @var Province */
            $province = Province::query()->create($province);

            $regencies = \json_decode(
                file_get_contents("{$path}/{$province->code}/regencies.json"),
                true,
            );

            $province->regencies()->createMany($regencies);

            $districts = \json_decode(
                file_get_contents("{$path}/{$province->code}/districts.json"),
                true,
            );

            District::query()->insert($districts);

            $villages = \json_decode(
                file_get_contents("{$path}/{$province->code}/villages.json"),
                true,
            );

            Village::query()->insert($villages);
        }

        return 0;
    }

    private function migrateIfNotMigrated(): void
    {
        $nusa = \config('database.connections.nusa');

        if (file_exists($nusa['database'])) {
            @\unlink($nusa['database']);
        }

        @\touch($nusa['database']);

        $this->call('migrate:fresh', [
            '--realpath' => true,
            '--path' => [
                $this->libPath.'/database/migrations/create_nusa_tables.php',
                $this->libPath.'/database/migrations/create_testing_tables.php',
            ],
        ]);
    }
}
