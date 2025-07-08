<?php

declare(strict_types=1);

namespace Workbench\App\Console;

use Illuminate\Console\Command;
use Illuminate\Console\View\Components\TwoColumnDetail;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;
use PDO;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Statements\DeleteStatement;
use Symfony\Component\Finder\Finder;
use Workbench\App\Support\Normalizer;

class ImportCommand extends Command
{
    use CommandHelpers;

    private int $chunkSize = 5_000;

    protected $signature = 'nusa:import
                            {--fresh : Refresh database migrations and seeders}
                            {--dist : Generate distribution database}';

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

                if (str_contains($path, 'boundaries')) {
                    $parser = new Parser($query);
                    $tasks = [];

                    foreach ($parser->statements as $statement) {
                        if ($statement instanceof DeleteStatement) {
                            continue;
                        }

                        $tasks[] = fn () => $conn->query((string) $statement);
                    }

                    Concurrency::driver('fork')->run($tasks);
                } else {
                    $conn->query($query);
                }

                $timer->stop();
            }
        });

        if ($this->option('fresh')) {
            $this->refreshDatabase();
        }

        $this->group('Seeding from upstream');

        foreach ($this->fetchAll() as $table => $values) {
            $count = count($values);
            $timer = $this->timer(
                "Seeding <fg=yellow>{$count}</> data to '<fg=yellow>{$table}</>'"
            );

            foreach (array_chunk($values, $this->chunkSize) as $chunks) {
                DB::transaction(fn () => $this->model($table)->insert($chunks));

                unset($chunks);
            }

            $timer->stop();
        }

        $this->endGroup();

        if ($this->option('dist')) {
            $this->call(DistCommand::class, ['--force' => true]);
        }
    }

    private function refreshDatabase(): void
    {
        $this->group('Recreating database');

        $this->recreateDatabaseFile();

        $this->callSilent('vendor:publish', ['--tag' => 'creasi-migrations']);
        $this->call('migrate:fresh');
    }

    private function fetchAll(): array
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

        $outputs = [];
        $timer = $this->timer('Normalizing fetched data');

        foreach (array_chunk($data, $this->chunkSize) as $chunks) {
            foreach ($chunks as $item) {
                $normalizer = new Normalizer(
                    $item->kode,
                    $item->nama,
                    $item->kodepos,
                    $item->lat,
                    $item->lng,
                    $item->path
                );

                if ($normalized = $normalizer->normalize()) {
                    $outputs[$normalizer->type][] = $normalized;
                }
            }

            unset($chunks);
        }

        $timer->stop();

        return $outputs;
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

    /**
     * @return PDO|array|void
     */
    private function upstream(string|\Closure|null $statement = null)
    {
        $config = config('database.connections.upstream', []);

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
        $path = config('database.connections.nusa.database');

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
