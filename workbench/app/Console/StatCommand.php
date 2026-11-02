<?php

declare(strict_types=1);

namespace Workbench\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use PhpMyAdmin\SqlParser\Statements\DeleteStatement;
use PhpMyAdmin\SqlParser\Statements\InsertStatement;
use PhpMyAdmin\SqlParser\Statements\UpdateStatement;
use Symfony\Component\Process\Process;
use Workbench\App\Support\GitHelper;
use Workbench\App\Support\SqlHelper;

class StatCommand extends Command
{
    use CommandHelpers, GitHelper, SqlHelper;

    protected $signature = 'nusa:stat
        {--output= : Write a Markdown report to this path (empty when there are no changes)}';

    protected $description = 'Generate stats of imported database from upstream';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Database stats');

        $header = ['code', 'name'];
        $hasChanges = false;
        $report = [];
        $branch = $this->currentBranch();

        $diffs = $this->getDiffs($branch);

        if ($diffs === null) {
            $this->line("<error> ERROR </> Cannot open database file for <fg=yellow>{$branch}</>, please run the following command if necessary:");
            $this->line(' - <fg=yellow>vendor/bin/testbench nusa:import --fresh --dist</>');

            return 1;
        }

        foreach ($diffs as $table => $diff) {
            $columns = ['code', 'name', 'latitude', 'longitude'];

            if ($table === 'villages') {
                $columns[] = 'postal_code';
            }

            $changed = $this->marshalChanges($diff['changed'], $table, $columns);
            $added = $this->marshalAdditions($diff['added'], $table);
            $deleted = $this->marshalDeletion($diff['deleted'], $table);
            $moved = [];

            foreach ($deleted as $d => [$dCode, $dName]) {
                $move = array_filter($added, fn ($arr) => $arr[1] === $dName);

                if (empty($move)) {
                    continue;
                }

                $a = key($move);
                $moved[] = [
                    'code' => "{$dCode} <fg=yellow>→</> <fg=green>{$move[$a][0]}</>",
                    'name' => $move[$a][1],
                ];

                unset($deleted[$d], $added[$a]);
            }

            $addedCount = \count($added);
            $changedCount = \count($changed);
            $movedCount = \count($moved);
            $deletedCount = \count($deleted);

            $report[$table] = [
                'columns' => $columns,
                'added' => $added,
                'moved' => $moved,
                'changed' => $changed,
                'deleted' => $deleted,
            ];

            if (($addedCount + $changedCount + $movedCount + $deletedCount) === 0) {
                $this->line("<fg=green>{$table}</>: No database changes detected");

                continue;
            }

            $this->group("<fg=green>{$table}</>: <fg=yellow>{$addedCount}</> new, <fg=yellow>{$movedCount}</> moved, <fg=yellow>{$changedCount}</> changes and <fg=yellow>{$deletedCount}</> deleted");

            if ($addedCount > 0) {
                $this->info('Added');
                $this->table($header, $added);

                $hasChanges = true;
            }

            if ($deletedCount > 0) {
                $this->info('Deleted');
                $this->table($header, $deleted);

                $hasChanges = true;
            }

            if ($movedCount > 0) {
                $this->info('Moved');
                $this->table($header, $moved);

                $hasChanges = true;
            }

            if ($changedCount > 0) {
                $this->info('Changed');
                $this->table($columns, $changed);

                $hasChanges = true;
            }
        }

        $this->endGroup();

        if ($output = $this->option('output')) {
            $markdown = $this->renderMarkdown(array_filter(
                $report,
                fn (array $diff) => $this->totalChanges($diff) > 0
            ));

            if (file_put_contents($output, $markdown) === false) {
                $this->error("Cannot write report to {$output}");
            }
        }

        $this->ciOutput($hasChanges ? 'has-changes=1' : 'has-changes=0');

        return 0;
    }

    /**
     * @param  array{columns: array<int, string>, added: array, moved: array, changed: array, deleted: array}  $diff
     */
    private function totalChanges(array $diff): int
    {
        return \count($diff['added']) + \count($diff['moved'])
            + \count($diff['changed']) + \count($diff['deleted']);
    }

    /**
     * @param  array<string, array{columns: array<int, string>, added: array, moved: array, changed: array, deleted: array}>  $tables
     */
    private function renderMarkdown(array $tables): string
    {
        if ($tables === []) {
            return '';
        }

        $labels = [
            'provinces' => ['Province', 'provinces'],
            'regencies' => ['Regency', 'regencies'],
            'districts' => ['District', 'districts'],
            'villages' => ['Village', 'villages'],
        ];

        $summary = [];

        foreach ($tables as $table => $diff) {
            $count = $this->totalChanges($diff);

            $summary[] = $count.' '.($count === 1 ? strtolower($labels[$table][0]) : $labels[$table][1]);
        }

        $last = array_pop($summary);
        $heading = $summary === [] ? $last : implode(', ', $summary).' and '.$last;

        $lines = ['<!-- nusa-stat -->', "### {$heading} got updated", ''];

        foreach ($tables as $table => $diff) {
            $counts = [
                \count($diff['added']),
                \count($diff['moved']),
                \count($diff['changed']),
                \count($diff['deleted']),
            ];

            $lines[] = \sprintf(
                '<details><summary>%s: %d new, %d moved, %d changed and %d deleted</summary>',
                $labels[$table][0],
                ...$counts
            );
            $lines[] = '';
            $lines[] = $this->markdownTable($diff);
            $lines[] = '';
            $lines[] = '</details>';
            $lines[] = '';
        }

        return implode(\PHP_EOL, $lines);
    }

    /**
     * @param  array{columns: array<int, string>, added: array, moved: array, changed: array, deleted: array}  $diff
     */
    private function markdownTable(array $diff): string
    {
        $columns = $diff['columns'];
        $rows = [];

        foreach ($diff['added'] as $row) {
            $rows[] = $this->markdownRow('new', [$row[0], $row[1]], $columns);
        }

        foreach ($diff['moved'] as $row) {
            $rows[] = $this->markdownRow('moved', [$row['code'], $row['name']], $columns);
        }

        foreach ($diff['changed'] as $row) {
            $rows[] = $this->markdownRow('changed', array_map(
                fn (string $column) => $row[$column] ?? '',
                $columns
            ), $columns);
        }

        foreach ($diff['deleted'] as $row) {
            $rows[] = $this->markdownRow('deleted', [$row[0], $row[1]], $columns);
        }

        $hidden = max(0, \count($rows) - 100);
        $rows = \array_slice($rows, 0, 100);

        $lines = [
            '| state | '.implode(' | ', $columns).' |',
            '| --- | '.implode(' | ', array_map(
                fn (string $column) => match ($column) {
                    'latitude', 'longitude' => '---:',
                    'postal_code' => ':---:',
                    default => '---',
                },
                $columns
            )).' |',
        ];

        foreach ($rows as $row) {
            $lines[] = '| '.implode(' | ', array_map([$this, 'markdownValue'], $row)).' |';
        }

        if ($hidden > 0) {
            $lines[] = '';
            $lines[] = "_and {$hidden} more_";
        }

        return implode(\PHP_EOL, $lines);
    }

    /**
     * @param  array<int, string>  $values
     * @param  array<int, string>  $columns
     * @return array<int, string>
     */
    private function markdownRow(string $state, array $values, array $columns): array
    {
        $row = [$state];

        foreach (array_keys($columns) as $i) {
            $row[] = $values[$i] ?? '';
        }

        return $row;
    }

    private function markdownValue(?string $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $value = preg_replace('/<fg=green>(.*?)<\/>/s', '**$1**', $value);
        $value = preg_replace('/<fg=red>(.*?)<\/>/s', '~~$1~~', $value);
        $value = str_replace('<fg=yellow>→</>', '→', $value);
        $value = preg_replace('/<\/?[^>]+>/', '', $value);

        return str_replace('|', '\|', (string) $value);
    }

    /**
     * @return null|array{districts: array, provinces: array, regencies: array, villages: array}
     */
    public function getDiffs(string $branch): ?array
    {
        $current = $this->libPath('database', 'nusa.sqlite');
        $updated = $this->libPath('database', "nusa.{$branch}.sqlite");
        $reports = $states = [
            'added' => [],
            'changed' => [],
            'deleted' => [],
        ];

        if (! file_exists((string) $updated)) {
            return null;
        }

        $output = $this->runDiff((string) $current, (string) $updated);

        if ($output === null) {
            return null;
        }

        foreach ($this->parseQuery($output) as $statement) {
            if ($statement instanceof InsertStatement) {
                $reports['added'][$statement->into->dest->table][] = array_map(
                    [$this, 'sanitizeValue'],
                    $statement->values[0]->values
                );

                continue;
            }

            if ($statement instanceof UpdateStatement) {
                [$field, $value] = explode('=', $statement->where[0]->expr, 2);

                $changes = [
                    trim($field) => $this->sanitizeValue($value),
                ];

                foreach ($statement->set as $set) {
                    $changes[$set->column] = $this->sanitizeValue($set->value);
                }

                $reports['changed'][$statement->tables[0]->expr][] = $changes;

                continue;
            }

            if ($statement instanceof DeleteStatement) {
                [$field, $value] = explode('=', $statement->where[0]->expr, 2);

                $reports['deleted'][$statement->from[0]->table][] = [
                    trim($field) => $this->sanitizeValue($value),
                ];

                continue;
            }
        }

        $output = [
            'provinces' => $states,
            'regencies' => $states,
            'districts' => $states,
            'villages' => $states,
        ];

        foreach ($reports as $state => $report) {
            foreach ($report as $table => $statement) {
                $output[$table][$state] = $statement;
            }
        }

        return $output;
    }

    /**
     * Run `sqldiff` and return its raw output.
     */
    protected function runDiff(string $current, string $updated): ?string
    {
        $process = new Process(['sqldiff', '--primarykey', $current, $updated]);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('sqldiff failed with exit code '.$process->getExitCode().':');
            $this->error($process->getErrorOutput() ?: $process->getOutput());

            return null;
        }

        return $process->getOutput();
    }

    /**
     * @return array{code: string, name: string, latitude?: float, longitude?: float, postal_code?: string}
     */
    private function marshalChanges(array $changes, string $table, array $columns): array
    {
        if (empty($changes)) {
            return [];
        }

        $result = DB::connection('current')
            ->table($table)
            ->whereIn('code', Arr::pluck($changes, 'code'))
            ->get($columns);

        return $result->map(function ($row, $i) use ($table, $changes) {
            $res = [
                'code' => $row->code,
                'name' => $row->name,
                'latitude' => $row->latitude,
                'longitude' => $row->longitude,
            ];

            if ($table === 'villages') {
                $res['postal_code'] = $row->postal_code;
            }

            foreach ($res as $field => $value) {
                if (! \array_key_exists($field, $changes[$i])) {
                    continue;
                }

                $newValue = $changes[$i][$field];

                if (\in_array($field, ['latitude', 'longitude'], true) && $newValue) {
                    $newValue = (float) $newValue;
                }

                if ($newValue === $value) {
                    continue;
                }

                if (! $value && $newValue) {
                    $res[$field] = "<fg=green>{$newValue}</>";

                    continue;
                }

                if ($res[$field] && ! $newValue) {
                    $res[$field] = "<fg=red>{$value}</>";

                    continue;
                }

                $res[$field] .= " <fg=yellow>→</> <fg=green>{$newValue}</>";
            }

            return $res;
        })->all();
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    private function marshalAdditions(array $adds, string $table): array
    {
        $result = [];

        foreach ($adds as $row) {
            $result[] = match ($table) {
                'provinces' => [$row[0], $row[1]],
                'regencies' => [$row[0], $row[2]],
                'districts' => [$row[0], $row[3]],
                'villages' => [$row[0], $row[4]],
            };
        }

        return $result;
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    private function marshalDeletion(array $deletes, string $table): array
    {
        if (empty($deletes)) {
            return [];
        }

        $result = DB::connection('current')
            ->table($table)
            ->whereIn('code', Arr::pluck($deletes, 'code'))
            ->get(['code', 'name']);

        return $result->map(fn ($row) => [$row->code, $row->name])->all();
    }

    private function sanitizeValue(string $value): ?string
    {
        $value = trim(trim($value), "'");

        if ($value === 'NULL') {
            $value = null;
        }

        return $value;
    }
}
