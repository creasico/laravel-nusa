<?php

namespace Creasi\Scripts;

use Illuminate\Support\Collection;
use PDO;
use PDOStatement;

class Database
{
    private readonly PDO $conn;

    private readonly string $libPath;

    public function __construct(string $name, string $host, string $user, ?string $pass = null)
    {
        $this->libPath = \realpath(\dirname(__DIR__.'/../..'));

        $this->conn = new PDO("mysql:dbname={$name};host={$host}", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }

    public function query(string $statement, ?int $mode = null, mixed ...$args): PDOStatement
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

        $self = new static(
            name: env('DB_NAME', 'nusantara'),
            host: env('DB_HOST', '127.0.0.1'),
            user: env('DB_USER', 'root'),
            pass: env('DB_PASS', 'secret'),
        );

        if ($wilayahSql = file_get_contents($self->libPath.'/submodules/cahyadsn-wilayah/db/wilayah.sql')) {
            $self->query($wilayahSql);
        }

        if ($coordinatesSql = file_get_contents($self->libPath.'/submodules/cahyadsn-wilayah/db/wilayah_level_1_2.sql')) {
            $self->query($coordinatesSql);
        }

        if ($postalSql = file_get_contents($self->libPath.'/submodules/w3appdev-kodepos/kodewilayah2023.sql')) {
            $self->query($postalSql);
        }

        $stmt = $self->query(<<<'SQL'
            SELECT
                w.kode, w.nama,
                p.kodepos,
                l.lat, l.lng, l.elv, l.tz, l.luas, l.penduduk, l.path
            FROM wilayah w
            LEFT JOIN wilayah_level_1_2 l ON w.kode = l.kode
            LEFT JOIN kodewilayah2023 p on w.kode = p.kodewilayah
            ORDER BY w.kode
        SQL, PDO::FETCH_OBJ);

        $data = collect($stmt->fetchAll())->reduce(function ($regions, $item) {
            $data = new Normalizer($item->kode, $item->nama, $item->kodepos, $item->lat, $item->lng, $item->path);

            $regions[$data->type][] = $data->normalize();

            return $regions;
        }, []);

        foreach ($data as $table => $content) {
            $content = \collect($content);

            if ($table === 'provinces') {
                $self->writeCsv($table, $content);

                $self->writeJson($table, $content);

                continue;
            }

            $content->groupBy('province_code')->each(function (Collection $content, $key) use ($table, $self) {
                $self->writeCsv($key.'/'.$table, $content);

                $self->writeJson($key.'/'.$table, $content);
            });
        }
    }

    private function writeCsv(string $filename, Collection $content): void
    {
        $this->ensureDirectoryExists(
            $path = "{$this->libPath}/resources/csv/$filename.csv"
        );

        $csv = [
            array_keys($content[0]),
        ];

        foreach ($content as $value) {
            if (isset($value['coordinates'])) {
                $value['coordinates'] = \json_encode($value['coordinates']);
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
            $path = "{$this->libPath}/resources/json/$filename.json"
        );

        file_put_contents($path, json_encode($content->toArray(), JSON_PRETTY_PRINT));
    }

    private function ensureDirectoryExists(string $path)
    {
        $dir = \dirname($path);

        if (! is_dir($dir)) {
            \mkdir($dir, 0755);
        }
    }
}
