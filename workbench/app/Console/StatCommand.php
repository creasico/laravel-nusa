<?php

namespace Workbench\App\Console;

use Creasi\Nusa\Contracts\Province;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;

class StatCommand extends Command
{
    protected $signature = 'nusa:stat
                            {--diff : Generate diff from existing stats}';

    protected $description = 'Generate stats after test';

    /**
     * Execute the console command.
     */
    public function handle(Province $province): void
    {
        $table = [
            'code' => 'Code',
            'name' => 'Name',
            'regencies_count' => 'Regencies',
            'districts_count' => 'Districts',
            'villages_count' => 'Villages',
        ];

        $rows = $this->getStats($province, \array_keys($table));

        if ($diffs = $this->getDiffs($rows)) {
            $this->info('Changes');
            $this->table(\array_values($table), $diffs);

            return;
        }

        File::put($this->getStorePath(), \json_encode($rows, \JSON_PRETTY_PRINT));

        $this->table(\array_values($table), $rows);
    }

    private function getDiffs(array $rows): ?array
    {
        if (! $this->option('diff')) {
            return null;
        }

        try {
            $storedStat = File::json($this->getStorePath());
            $diffs = \array_filter($rows, fn ($row, $key) => $storedStat[$key] !== $row, \ARRAY_FILTER_USE_BOTH);

            if (empty($diffs)) {
                return null;
            }

            return $diffs;
        } catch (FileNotFoundException $err) {
            $this->warn($err->getMessage());

            return null;
        }
    }

    private function getStats(Province $province, array $fields): array
    {
        $stats = $province->withCount(['regencies', 'districts', 'villages'])->get(['code', 'name']);
        $rows = [];

        foreach ($stats as $stat) {
            $row = [];

            foreach ($fields as $field) {
                $row[$field] = $stat->$field;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function getStorePath(): string
    {
        $storePath = \realpath(\dirname(__DIR__).'/../../tests');

        return $storePath.'/stats.json';
    }
}
