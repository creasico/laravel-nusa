<?php

namespace Creasi\Scripts;

use Dotenv\Dotenv;
use Illuminate\Support\Collection;
use PDO;
use PDOStatement;

class Database
{
    private readonly PDO $conn;

    private readonly string $libPath;

    public function __construct(string $name, string $host, string $user, ?string $pass = null)
    {
        $this->libPath = \realpath(\dirname(__DIR__));

        $this->conn = new PDO("mysql:dbname={$name};host={$host}", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }

    private function query(string $statement, ?int $mode = null, mixed ...$args): PDOStatement
    {
        return $this->conn->query($statement, $mode, ...$args);
    }

    /**
     * Handle the post-install Composer event.
     *
     * @param  \Composer\Script\Event  $event
     */
    public static function import($event): void
    {
        require_once $event->getComposer()->getConfig()->get('vendor-dir').'/autoload.php';

        Dotenv::createImmutable(\dirname(__DIR__).'/workbench')->safeLoad();

        $db = new static(
            name: env('DB_NUSA', 'nusantara'),
            host: env('DB_HOST', '127.0.0.1'),
            user: env('DB_USERNAME', 'root'),
            pass: env('DB_PASSWORD'),
        );

        $db->importSql(
            'cahyadsn-wilayah/db/wilayah.sql',
            'cahyadsn-wilayah/db/archive/wilayah_level_1_2.sql',
            'w3appdev-kodepos/kodewilayah2023.sql',
        );

        foreach ($db->fetchAll() as $table => $content) {
            $content = \collect($content);

            if ($table === 'provinces') {
                $db->writeCsv($table, $content);

                $db->writeJson($table, $content);

                continue;
            }

            $content->groupBy('province_code')->each(function (Collection $content, $key) use ($table, $db) {
                $db->writeCsv($key.'/'.$table, $content);

                $db->writeJson($key.'/'.$table, $content);
            });
        }

        exit(0);
    }

    private function fetchAll(): array
    {
        $stmt = $this->query(<<<'SQL'
            SELECT
                w.kode, w.nama,
                p.kodepos,
                l.lat, l.lng, l.elv, l.tz, l.luas, l.penduduk, l.paths path
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
            if ($query = file_get_contents("{$this->libPath}/submodules/{$path}")) {
                $this->query($query);
            }
        }
    }

    private function writeCsv(string $filename, Collection $content): void
    {
        $this->ensureDirectoryExists(
            $path = "{$this->libPath}/resources/static/{$filename}.csv"
        );

        $csv = [
            array_keys($content[0]),
        ];

        foreach ($content as $value) {
            if (isset($value['coordinates'])) {
                unset($value['coordinates']);
                // $value['coordinates'] = \json_encode($value['coordinates']);
            }

            $csv[] = array_values($value);
        }

        $fp = fopen($path, 'w');

        foreach ($csv as $line) {
            fputcsv($fp, $line);
        }

        fclose($fp);
    }

    private function writeJson(string $filename, Collection $content): void
    {
        $this->ensureDirectoryExists(
            $path = "{$this->libPath}/resources/static/{$filename}.json"
        );

        file_put_contents($path, json_encode($content->map(function ($value) {
            if (isset($value['coordinates'])) {
                unset($value['coordinates']);
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
