<?php

namespace Workbench\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PDO;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
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
            'cahyadsn-wilayah_kodepos/db/wilayah_kodepos.sql',
        );

        $this->importSqlBoundaries();

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
                l.lat, l.lng, l.path path
            FROM wilayah w
            LEFT JOIN wilayah_boundaries l ON w.kode = l.kode
            LEFT JOIN wilayah_kodepos p on w.kode = p.kode
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

    private function importSqlBoundaries()
    {
        $path = 'workbench/submodules/cahyadsn-wilayah_boundaries';
        // $schema = file_get_contents(
        //     (string) $this->libPath($path, $schemaPath = 'db/ddl_wilayah_boundaries.sql')
        // );

        // $this->line(" - Importing 'cahyadsn-wilayah_boundaries/{$schemaPath}'");
        // $lines = explode("\n", explode('-- ', $schema)[1]);
        // $schema = implode("\n", array_slice($lines, 1, count($lines)));

        $this->line(" - Importing 'cahyadsn-wilayah_boundaries/db/ddl_wilayah_boundaries.sql'");
        $this->query(<<<'SQL'
            DROP TABLE IF EXISTS `wilayah_boundaries`;
            CREATE TABLE `wilayah_boundaries` (
                `kode` varchar(13) NOT NULL,
                `nama` varchar(100) DEFAULT NULL,
                `lat` double DEFAULT NULL,
                `lng` double DEFAULT NULL,
                `path` longtext,
                `status` int DEFAULT NULL,
                UNIQUE KEY `wilayah_boundaries_kode_IDX` (`kode`) USING BTREE
            )
        SQL);

        $sqls = Finder::create()
            ->files()
            ->in($path.'/db/*/')
            ->name('*.sql')
            ->filter(static function (SplFileInfo $file) {
                return ! in_array($file->getFilename(), [
                    'wilayah_boundaries_kab_75.sql',
                ]);
            });

        foreach ($sqls as $sqlPath => $sql) {
            $sqlPath = substr($sqlPath, 21);
            $this->line(" - Importing '{$sqlPath}'");
            $this->query($sql->getContents());
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
