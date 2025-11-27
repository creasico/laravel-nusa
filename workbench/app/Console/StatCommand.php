<?php

declare(strict_types=1);

namespace Workbench\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Statements\DeleteStatement;
use PhpMyAdmin\SqlParser\Statements\InsertStatement;
use PhpMyAdmin\SqlParser\Statements\UpdateStatement;
use Workbench\App\Support\GitHelper;

class StatCommand extends Command
{
    use CommandHelpers, GitHelper;

    protected $signature = 'nusa:stat';

    protected $description = 'Generate stats of imported database from upstream';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Database stats');

        $header = ['code', 'name'];
        $hasChanges = false;
        $diffs = $this->getDiffs();

        if ($diffs === null) {
            $this->line('<error> ERROR </> Cannot open database file, please run the following command if necessary:');
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
                    'code' => "{$dCode} <fg=yellow>→</> {$move[$a][0]}",
                    'name' => $move[$a][1],
                ];

                unset($deleted[$d], $added[$a]);
            }

            $addedCount = \count($added);
            $changedCount = \count($changed);
            $movedCount = \count($moved);
            $deletedCount = \count($deleted);

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

        if ($this->runningInCI()) {
            if ($hasChanges) {
                exec('echo "has-changes=1" >> $GITHUB_OUTPUT');
            } else {
                exec('echo "has-changes=0" >> $GITHUB_OUTPUT');
            }
        }

        return 0;
    }

    /**
     * @return null|array{districts: array, provinces: array, regencies: array, villages: array}
     */
    public function getDiffs(): ?array
    {
        $current = $this->libPath('database', 'nusa.sqlite');
        $updated = $this->libPath('database', "nusa.{$this->currentBranch()}.sqlite");
        $reports = $states = [
            'added' => [],
            'changed' => [],
            'deleted' => [],
        ];

        if (! file_exists((string) $updated)) {
            return null;
        }

        $parser = new Parser(shell_exec("sqldiff --primarykey {$current} {$updated}"));

        foreach ($parser->statements as $statement) {
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
                    $field => $this->sanitizeValue($value),
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
                    $field => $this->sanitizeValue($value),
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
                if (! array_key_exists($field, $changes[$i])) {
                    continue;
                }

                $newValue = $changes[$i][$field];

                if (in_array($field, ['latitude', 'longitude'], true) && $newValue) {
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

                $res[$field] .= " <fg=yellow>→</> {$newValue}";
            }

            return $res;
        })->all();
    }

    /**
     * @return array{0: string, 1: string}
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
     * @return array{0: string, 1: string}
     */
    private function marshalDeletion(array $deletes, string $table): array
    {
        $result = DB::connection('current')
            ->table($table)
            ->whereIn('code', Arr::pluck($deletes, 'code'))
            ->get(['code', 'name']);

        return $result->map(fn ($row) => [$row->code, $row->name])->all();
    }

    private function sanitizeValue(string $value): ?string
    {
        $value = trim($value, "'");

        if ($value === 'NULL') {
            $value = null;
        }

        return $value;
    }
}
