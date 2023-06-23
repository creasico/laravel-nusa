<?php

namespace Creasi\Scripts;

use Creasi\Nusa\Models;
use Illuminate\Support\Collection;
use PDO;

class Database
{
    private PDO $db;

    private function __construct()
    {
        $this->db = new PDO('sqlite:database/nusa.sqlite', options: [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }

    public static function sync($event)
    {
        require_once $event->getComposer()->getConfig()->get('vendor-dir').'/autoload.php';

        $models = [
            'provinces' => Models\Province::class,
            'regencies' => Models\Regency::class,
            'districts' => Models\District::class,
            'villages' => Models\Village::class,
        ];

        $self = new static;

        foreach ($self->fetch() as $table => $content) {
            $self->writeCsv($table, $content);

            $self->writeJson($table, $content);

            // $self->db->exec("CREATE TABLE IF NOT EXISTS {$table}");

            // $model = $models[$table];

            // if ($table !== 'villages') {
            //     $model::insert($content);
            //     continue;
            // }

            // \collect($content)->groupBy('district_code')->each(function (Collection $chunk) use ($model) {
            //     $model::insert($chunk->take(10)->toArray());
            // });
        }
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
        $db = new PDO('mysql:dbname=cahyadsn_wilayah;host=127.0.0.1', 'root', 'secret', [
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
