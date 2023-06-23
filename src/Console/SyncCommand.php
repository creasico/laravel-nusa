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
    protected $signature = 'nusa:sync
                            {dbname : Database name}
                            {--host=127.0.0.1 : Database host}
                            {--user=root : Database user}
                            {--pass= : Database pass}';

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
        $models = [
            'provinces' => Province::class,
            'regencies' => Regency::class,
            'districts' => District::class,
            'villages' => Village::class,
        ];

        $this->libPath = \realpath(\dirname(__DIR__).'/..');

        $this->migrateIfNotMigrated();

        foreach ($this->fetch() as $table => $content) {
            $this->writeCsv($table, $content);

            $this->writeJson($table, $content);

            $model = $models[$table];

            if ($table !== 'villages') {
                $model::insert($content);

                continue;
            }

            \collect($content)->groupBy('district_code')->each(function (Collection $chunk) use ($model) {
                $model::insert($chunk->toArray());
            });
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
            '--path' => $this->libPath.'/database/migrations/create_nusa_tables.php',
        ]);
    }

    private function writeCsv(string $filename, array $content): void
    {
        $csv = [
            array_keys($content[0]),
        ];

        foreach ($content as $value) {
            $csv[] = array_values($value);
        }

        $fp = fopen("{$this->libPath}/resources/csv/$filename.csv", 'w');

        foreach ($csv as $line) {
            fputcsv($fp, $line);
        }

        fclose($fp);
    }

    private function writeJson(string $filename, array $content): void
    {
        file_put_contents("{$this->libPath}/resources/json/$filename.json", json_encode($content, JSON_PRETTY_PRINT));
    }

    /**
     * @return array<string, array>
     */
    private function fetch(): array
    {
        $name = $this->argument('dbname');
        $host = $this->option('host');

        $db = new PDO("mysql:dbname={$name};host={$host}", $this->option('user'), $this->option('pass'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $stmt = $db->query('SELECT * from wilayah', PDO::FETCH_OBJ);

        return collect($stmt->fetchAll())->reduce(function ($regions, $item) {
            $data = new Normalizer($item->kode, $item->nama);

            $regions[$data->type][] = $data->normalize();

            return $regions;
        }, []);
    }
}
