<?php

namespace Workbench\App\Console;

use Creasi\Nusa\Contracts\Province;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
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
        $storePath = \realpath(\dirname(__DIR__).'/../../tests');

        if ($this->option('diff')) {
            $storedStat = File::json($storePath.'/stats.json');
            $diffs = \array_filter($rows, fn ($row, $key) => $storedStat[$key] !== $row, \ARRAY_FILTER_USE_BOTH);

            if (! empty($diffs)) {
                $this->info('Changes');
                $this->table(\array_values($table), $diffs);
            } else {
                $this->info('Unchanged');
            }
        } else {
            File::put(
                $storePath.'/stats.json',
                \json_encode($rows, \JSON_PRETTY_PRINT)
            );

            $this->table(\array_values($table), $rows);
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
}
