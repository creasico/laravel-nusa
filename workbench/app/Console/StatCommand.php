<?php

namespace Workbench\App\Console;

use Creasi\Nusa\Contracts\Province;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;

class StatCommand extends Command
{
    use CommandHelpers;

    protected $signature = 'nusa:stat
                            {--d|diff : Generate diff from existing stats}
                            {--w|write : Write latest stat to file}';

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

        $this->group('Database stats');

        $rows = $this->getStats($province, \array_keys($table));
        $diff = $this->option('diff') ?: false;
        $diffs = $diff ? $this->getDiffs($rows) : [];

        if ($this->option('write')) {
            File::put($this->libPath('tests/stats.json'), \json_encode($rows, \JSON_PRETTY_PRINT));
        }

        $this->table(\array_values($table), $this->calculate($rows, $diffs));

        $this->endGroup();
    }

    private function calculate(array $rows, array $diffs = []): array
    {
        if (empty($diffs)) {
            return $rows;
        }

        $out = [];
        $fields = ['regencies_count', 'districts_count', 'villages_count'];
        $diffs = collect($diffs);

        foreach ($rows as $i => $row) {
            $diff = $diffs->first(static fn ($diff) => $diff['code'] === $row['code']);

            if ($diff === null) {
                $out[$i] = $row;

                continue;
            }

            foreach ($row as $field => $value) {
                if (! in_array($field, $fields)) {
                    $out[$i][$field] = '<fg=yellow>'.$value.'</>';

                    continue;
                }

                $delta = $value - $diff[$field];
                $out[$i][$field] = $delta !== 0
                    ? '<fg=yellow>'.$value.' (</>'.$delta.'<fg=yellow>)</>'
                    : "<fg=yellow>{$value}</>";
            }
        }

        return $out;
    }

    private function getDiffs(array $rows): array
    {
        try {
            $diffs = \array_filter(
                File::json($this->libPath('tests/stats.json')),
                static fn ($row, $key) => $rows[$key] !== $row, \ARRAY_FILTER_USE_BOTH
            );

            return $diffs;
        } catch (FileNotFoundException $err) {
            $this->warn($err->getMessage());

            return [];
        }
    }

    private function getStats(Province $province, array $fields): array
    {
        $rows = [];
        $stats = $province->withCount(['regencies', 'districts', 'villages'])
            ->get(['code', 'name']);

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
