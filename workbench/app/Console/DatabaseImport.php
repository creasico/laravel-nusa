<?php

namespace Workbench\App\Console;

use Creasi\Nusa\Models;
use Illuminate\Console\Command;
use Illuminate\Console\View\Components\TwoColumnDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;
use Symfony\Component\Finder\Finder;
use Workbench\App\Support\Normalizer;

class DatabaseImport extends Command
{
    use CommandHelpers;

    protected $signature = 'nusa:import
                            {--fresh : Refresh database migrations and seeders}';

    protected $description = 'Import upstream database';

    public function handle()
    {
        $this->group('Importing files');

        $this->upstream(function (PDO $conn) {
            $files = $this->scanSqlFiles([
                'cahyadsn-wilayah/db/wilayah.sql',
                'cahyadsn-wilayah_kodepos/db/wilayah_kodepos.sql',
            ]);

            foreach ($files as $path => $query) {
                $timer = $this->timer("Imported '<fg=yellow>{$path}</>'");

                $conn->query($query);

                $timer->stop();
            }
        });

        $this->refreshDatabase();

        $this->group('Seeding from upstream');

        foreach ($this->fetchAll() as $table => $values) {
            $timer = $this->timer(
                "Seeding <fg=yellow>{$values->count()}</> data to '<fg=yellow>{$table}</>'"
            );

            $values->chunk(2_000)->each(function ($values) use ($table) {
                $this->importFromUpstream($table, $values);
            });

            $timer->stop();
        }

        $this->endGroup();
    }

    public function importFromUpstream(string $table, Collection $values): void
    {
        DB::transaction(static function () use ($table, $values) {
            $query = match ($table) {
                'provinces' => Models\Province::query(),
                'regencies' => Models\Regency::query(),
                'districts' => Models\District::query(),
                'villages' => Models\Village::query(),
            };

            $query->insert(
                $values->map(static function (array $data) {
                    if (isset($data['coordinates'])) {
                        $data['coordinates'] = json_encode($data['coordinates']);
                    }

                    return $data;
                })->all()
            );
        });

        // if ($table === 'provinces') {
        //     $this->writeCsv($table, $values);

        //     $this->writeJson($table, $values);

        //     continue;
        // }

        // $values->groupBy('province_code')->each(function (Collection $content, string $key) use ($table) {
        //     $this->writeCsv($key.'/'.$table, $content);

        //     $this->writeJson($key.'/'.$table, $content);
        // });
    }

    private function refreshDatabase(): bool
    {
        if (! ($fresh = $this->option('fresh'))) {
            return false;
        }

        $this->group('Recreating database');

        $this->recreateDatabaseFile();

        $this->callSilent('vendor:publish', ['--tag' => 'creasi-migrations']);
        $this->call('migrate:fresh');

        return $fresh;
    }

    /**
     * @return \Generator<string, Collection<string, array>>
     */
    private function fetchAll(): \Generator
    {
        $timer = $this->timer('Fetching upstream data');

        $data = $this->upstream(<<<'SQL'
            SELECT
                w.kode, w.nama,
                p.kodepos,
                l.lat, l.lng, l.path
            FROM wilayah w
            LEFT JOIN wilayah_boundaries l ON w.kode = l.kode
            LEFT JOIN wilayah_kodepos p on w.kode = p.kode
            ORDER BY w.kode
        SQL);

        $timer->stop();

        $timer = $this->timer('Normalizing fetched data');

        $data = array_reduce($data, static function ($outputs, $item) {
            $data = new Normalizer(
                $item->kode,
                $item->nama,
                $item->kodepos,
                $item->lat,
                $item->lng,
                $item->path
            );

            if ($normalized = $data->normalize()) {
                $outputs[$data->type][] = $normalized;
            }

            return $outputs;
        }, []);

        $timer->stop();

        if (! empty(Normalizer::$invalid)) {
            dd(Normalizer::$invalid);
        }

        foreach ($data as $table => $content) {
            yield $table => collect($content);
        }
    }

    /**
     * @param  array<int, string>  $paths
     * @return \Generator<string, string>
     */
    private function scanSqlFiles(array $paths = []): \Generator
    {
        $libPath = $this->libPath('workbench/submodules');

        foreach ($paths as $path) {
            yield $path => file_get_contents((string) $libPath->append("/{$path}"));
        }

        // This should grab the exact line where the MySQL schema definition
        // See: https://github.com/cahyadsn/wilayah_boundaries/blob/4555b309/db/ddl_wilayah_boundaries.sql#L35-L44
        $boundariesPath = 'cahyadsn-wilayah_boundaries/db';
        $ddlPath = "{$boundariesPath}/ddl_wilayah_boundaries.sql";
        $lines = explode(PHP_EOL, explode('-- ', file_get_contents(
            (string) $libPath->append("/{$ddlPath}")
        ))[1]);

        yield $ddlPath => implode(PHP_EOL, array_slice($lines, 1, count($lines)));

        $sqls = Finder::create()
            ->files()
            ->in((string) $libPath->append("/{$boundariesPath}/*/"))
            ->name('*.sql');

        foreach ($sqls as $path => $file) {
            $path = substr($path, $libPath->length() + 1);

            yield $path => $file->getContents();
        }
    }

    private function writeCsv(string $filename, Collection $content): void
    {
        File::ensureDirectoryExists(
            $path = $this->libPath('resources/static', $filename.'.csv')
        );

        $this->line(" - Writing: '<fg=yellow>{$path->substr($this->libPath()->length() + 1)}</>'");

        $csv = [
            array_keys($content[0]),
        ];

        foreach ($content as $value) {
            if (isset($value['coordinates'])) {
                $value['coordinates'] = null; // \json_encode($value['coordinates']);
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
        File::ensureDirectoryExists(
            $path = $this->libPath('resources/static', $filename.'.json')
        );

        $this->line(" - Writing: '<fg=yellow>{$path->substr($this->libPath()->length() + 1)}</>'");

        file_put_contents((string) $path, json_encode($content->map(function ($value) {
            if (isset($value['coordinates'])) {
                $value['coordinates'] = null;
            }

            return $value;
        })->toArray(), JSON_PRETTY_PRINT));

        if ($filename !== 'provinces') {
            return;
        }

        foreach ($content as $value) {
            if (! $value['latitude'] || ! $value['longitude'] || empty($value['coordinates'])) {
                continue;
            }

            $this->writeGeoJson($filename, $value['code'].'/path', $value);
        }
    }

    private function writeGeoJson(string $kind, string $filename, array $value): void
    {
        File::ensureDirectoryExists(
            $path = $this->libPath('resources/static', $filename.'.geojson')
        );

        $this->line(" - Writing: '<fg=yellow>{$path->substr($this->libPath()->length() + 1)}</>'");

        file_put_contents((string) $path, json_encode([
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => [
                        'code' => $value['code'],
                        'kind' => $kind,
                        'name' => $value['name'],
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [
                            $value['longitude'],
                            $value['latitude'],
                        ],
                    ],
                ],
                [
                    'type' => 'Feature',
                    'properties' => [
                        'code' => $value['code'],
                        'kind' => $kind,
                        'name' => $value['name'],
                    ],
                    'geometry' => [
                        'type' => 'MultiPolygon',
                        'coordinates' => $value['coordinates'],
                    ],
                ],
            ],
        ]));
    }

    /**
     * @return PDO|array|void
     */
    private function upstream(string|\Closure|null $statement = null)
    {
        $config = config('database.connections')['upstream'];

        $conn = new PDO(
            "mysql:dbname={$config['database']};host={$config['host']}",
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]
        );

        if (! $statement) {
            return $conn;
        }

        if ($statement instanceof \Closure) {
            return $statement($conn);
        }

        $stmt = $conn->query($statement, PDO::FETCH_OBJ);

        return $stmt->fetchAll();
    }

    private function recreateDatabaseFile(): void
    {
        $path = config('database.connections.nusa', [])['database'];

        if (\file_exists($path)) {
            \unlink($path);
        }

        \touch($path);
    }

    private function timer(string $caption)
    {
        $column = new TwoColumnDetail($this->output);

        return new class($caption, $column)
        {
            private float $startTime;

            public function __construct(
                private string $caption,
                private TwoColumnDetail $column,
            ) {
                $this->startTime = microtime(true);
            }

            public function stop()
            {
                $runTime = number_format((microtime(true) - $this->startTime) * 1000);

                $this->column->render(
                    "  {$this->caption}",
                    "<fg=gray>{$runTime}ms</> <fg=green;options=bold>DONE</>"
                );
            }
        };
    }
}
