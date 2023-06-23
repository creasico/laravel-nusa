<?php

declare(strict_types=1);

namespace Creasi\Nusa\Console;

use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;
use Creasi\Nusa\Models\Village;
use Creasi\Scripts\Region;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use PDO;

class SyncCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'nusa:sync
                            {db_name : Database name}
                            {--host=127.0.0.1 : Database host}
                            {--user=root : Database user}
                            {--pass= : Database pass}';

    /**
     * @var string
     */
    protected $description = 'Sync database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $models = [
            'provinces' => Province::class,
            'regencies' => Regency::class,
            'districts' => District::class,
            'villages' => Village::class,
        ];

        foreach ($this->fetch() as $table => $content) {
            $this->writeCsv($table, $content);

            $this->writeJson($table, $content);

            $model = $models[$table];

            if ($table !== 'villages') {
                $model::insert(\json_decode($content, true));
                continue;
            }

            \collect(\json_decode($content, true))->groupBy('district_code')->each(function (Collection $chunk) use ($model) {
                $model::insert($chunk->take(10)->toArray());
            });
        }

        return 0;
    }

    private function writeCsv(string $filename, array $content)
    {
        $csv = [
            array_keys($content[0])
        ];

        foreach ($content as $value) {
            $csv[] = array_values($value);
        }

        $fp = fopen("database/csv/$filename.csv", 'w');

        foreach ($csv as $line) {
            fputcsv($fp, $line);
        }

        fclose($fp);
    }

    private function writeJson(string $filename, array $content)
    {
        file_put_contents("database/json/$filename.json", json_encode($content, JSON_PRETTY_PRINT));
    }

    private function fetch()
    {
        $name = $this->argument('db_name');
        $host = $this->option('host');

        $db = new PDO("mysql:dbname={$name};host={$host}", $this->option('user'), $this->option('pass'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $stmt = $db->query('SELECT * from wilayah', PDO::FETCH_OBJ);

        return collect($stmt->fetchAll())->reduce(function ($regions, $item) {
            $region = new Region($item->kode, $item->nama);

            $regions[$region->type][] = match ($region->type) {
                'villages' => $region->toVillage(),
                'districts' => $region->toDistrict(),
                'regencies' => $region->toRegency(),
                'provinces' => [
                    'code' => $region->code,
                    'name' => $region->name,
                ],
            };

            return $regions;
        }, []);
    }
}
