<?php

namespace Workbench\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PDO;
use Workbench\App\Support\Normalizer;

class DatabaseImport extends Command
{
    use CommandHelpers;

    protected $signature = 'nusa:import';

    protected $description = 'Import upstream database';

    private PDO $conn;

    public function __construct()
    {
        parent::__construct();

        try {
            $conn = DB::connection('upstream');

            $this->conn = new PDO(
                "mysql:dbname={$conn->getConfig('database')};host={$conn->getConfig('host')}",
                $conn->getConfig('username'),
                $conn->getConfig('password'),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]
            );
        } catch (\PDOException $_) {
            //
        }
    }

    public function handle()
    {
        $this->group('Importing files');

        $this->importSql(
            'cahyadsn-wilayah/db/wilayah.sql',
            'cahyadsn-wilayah/db/archive/wilayah_level_1_2.sql',
            'w3appdev-kodepos/kodewilayah2023.sql',
        );

        $this->group('Writing CSV and JSON files');

        foreach ($this->fetchAll() as $table => $content) {
            $content = \collect($content);

            if ($table === 'provinces') {
                $this->writeCsv($table, $content);

                $this->writeJson($table, $content);

                continue;
            }

            $content->groupBy('province_code')->each(function (Collection $content, string $key) use ($table) {
                $this->writeCsv($key.'/'.$table, $content);

                $this->writeJson($key.'/'.$table, $content);
            });
        }

        $this->endGroup();
    }

    private function fetchAll(): array
    {
        $stmt = $this->query(<<<'SQL'
            SELECT
                w.kode, w.nama,
                p.kodepos,
                l.lat, l.lng, l.elv, l.tz, l.luas, l.penduduk, l.path path
            FROM wilayah w
            LEFT JOIN wilayah_level_1_2 l ON w.kode = l.kode
            LEFT JOIN kodewilayah2023 p on w.kode = p.kodewilayah
            ORDER BY w.kode
        SQL, PDO::FETCH_OBJ);

        $data = collect($stmt->fetchAll())->reduce(function ($regions, $item) {
            $data = new Normalizer($item->kode, $item->nama, $item->kodepos, $item->lat, $item->lng, $item->path);

            if ($normalized = $data->normalize()) {
                $regions[$data->type][] = $normalized;
            }

            return $regions;
        }, []);

        if (! empty(Normalizer::$invalid)) {
            dd(Normalizer::$invalid);
        }

        return $data;
    }

    private function importSql(string ...$paths): void
    {
        foreach ($paths as $path) {
            $this->line(" - Importing '{$path}'");

            if ($query = file_get_contents((string) $this->libPath('workbench/submodules', $path))) {
                /* dd($query); */
                $this->query($query);
            }
        }
    }

    private function query(string $statement, ?int $mode = null, mixed ...$args)
    {
        return $this->conn->query($statement, $mode, ...$args);
    }

    private function writeCsv(string $filename, Collection $content): void
    {
        $this->ensureDirectoryExists(
            $path = $this->libPath('resources/static', $filename.'.csv')
        );

        $this->line(" - Writing: '{$path->substr(strlen($this->libPath()) + 1)}'");

        $csv = [
            array_keys($content[0]),
        ];

        foreach ($content as $value) {
            if (isset($value['coordinates'])) {
                $value['coordinates'] = null;
                // $value['coordinates'] = \json_encode($value['coordinates']);
            }

            $csv[] = array_values($value);
        }

        $fp = fopen((string) $path, 'w');

        foreach ($csv as $line) {
            fputcsv($fp, $line);
        }

        fclose($fp);
    }

    private function writeJson(string $filename, Collection $content): void
    {
        $this->ensureDirectoryExists(
            $path = $this->libPath('resources/static', $filename.'.json')
        );

        $this->line(" - Writing: '{$path->substr(strlen($this->libPath()) + 1)}'");

        file_put_contents((string) $path, json_encode($content->map(function ($value) {
            if (isset($value['coordinates'])) {
                $value['coordinates'] = null;
            }

            return $value;
        })->toArray(), JSON_PRETTY_PRINT));
    }

    private function ensureDirectoryExists(string $path)
    {
        $dir = \dirname($path);

        if (! is_dir($dir)) {
            \mkdir($dir, 0755);
        }
    }
}
