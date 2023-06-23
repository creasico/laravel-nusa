<?php

namespace Creasi\Scripts;

use PDO;

class Database
{
    private PDO $db;

    private string $path;

    private function __construct()
    {
        $this->db = new PDO('sqlite:database/nusa.sqlite', options: [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $this->path = \dirname(__DIR__).'/resources';
    }

    public static function sync($event)
    {
        require_once $event->getComposer()->getConfig()->get('vendor-dir').'/autoload.php';

        $self = new static;

        foreach ($self->fetch() as $table => $content) {
            $self->writeCsv($table, $content);

            $self->writeJson($table, $content);
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

        $fp = fopen("{$this->path}/csv/{$filename}.csv", 'w');

        foreach ($csv as $line) {
            fputcsv($fp, $line);
        }

        fclose($fp);
    }

    private function writeJson(string $filename, array $content)
    {
        file_put_contents("{$this->path}/json/{$filename}.json", json_encode($content, JSON_PRETTY_PRINT));
    }

    /**
     * @return array<string, array>
     */
    private function fetch(): array
    {
        $db = new PDO('mysql:dbname=cahyadsn_wilayah;host=127.0.0.1', 'root', 'secret', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $stmt = $db->query('SELECT * from wilayah', PDO::FETCH_OBJ);
        $results = [];

        foreach ($stmt->fetchAll() as $item) {
            $region = new Region($item->kode, $item->nama);

            $results[$region->type][] = match ($region->type) {
                'villages' => $region->toVillage(),
                'districts' => $region->toDistrict(),
                'regencies' => $region->toRegency(),
                'provinces' => [
                    'code' => (int) $region->code,
                    'name' => $region->name,
                ],
            };
        }

        return $results;
    }
}
