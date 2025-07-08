<?php

namespace Workbench\App\Console;

use Creasi\Nusa\Contracts\Province;
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
    public function handle(Province $province): void
    {
        $this->info('Database stats');

        $header = ['code', 'name'];

        foreach ($this->getDiffs() as $table => $diff) {
            $added = $changed = $moved = $deleted = [];

            if (array_key_exists('changed', $diff)) {
                $changes = $diff['changed'];
                $codes = Arr::pluck($changes, 'code');
                $columns = ['code', 'name', 'latitude', 'longitude'];

                if ($table === 'villages') {
                    $columns[] = 'postal_code';
                }

                $changed = DB::connection('current')
                    ->table($table)
                    ->whereIn('code', $codes)
                    ->get($columns)
                    ->map(function ($row, $i) use ($table, $changes) {
                        $res = [
                            'code' => $row->code,
                            'name' => $row->name,
                            'latitude' => $row->latitude ? (float) trim($row->latitude) : null,
                            'longitude' => $row->longitude ? (float) trim($row->longitude) : null,
                        ];

                        if ($table === 'villages') {
                            $res['postal_code'] = $row->postal_code;
                        }

                        foreach ($res as $field => $value) {
                            if (! isset($changes[$i][$field])) {
                                continue;
                            }

                            $newValue = $changes[$i][$field];

                            if (in_array($field, ['latitude', 'longitude'], true)) {
                                $newValue = (float) trim($newValue);
                            }

                            if ($newValue === $res[$field]) {
                                continue;
                            }

                            if (is_null($res[$field])) {
                                $res[$field] = "<fg=green>{$newValue}</>";
                            } else {
                                $res[$field] .= " <fg=yellow>→</> {$newValue}";
                            }
                        }

                        return $res;
                    });
            }

            if (array_key_exists('added', $diff)) {
                foreach ($diff['added'] as $row) {
                    $added[] = match ($table) {
                        'provinces' => [$row[0], $row[1]],
                        'regencies' => [$row[0], $row[2]],
                        'districts' => [$row[0], $row[3]],
                        'villages' => [$row[0], $row[4]],
                    };
                }
            }

            if (array_key_exists('deleted', $diff)) {
                $codes = Arr::pluck($diff['deleted'], 'code');
                $deleted = DB::connection('current')
                    ->table($table)
                    ->whereIn('code', $codes)
                    ->get(['code', 'name'])
                    ->map(fn ($row) => [$row->code, $row->name]);

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
            }

            $added_count = count($added);
            $changed_count = count($changed);
            $moved_count = count($moved);
            $deleted_count = count($deleted);

            $this->group("{$table}: <fg=yellow>{$added_count}</> new, <fg=yellow>{$moved_count}</> moved, <fg=yellow>{$changed_count}</> changes and <fg=yellow>{$deleted_count}</> deleted");

            if ($added_count > 0) {
                $this->info('Added');
                $this->table($header, $added);
            }

            if ($deleted_count > 0) {
                $this->info('Deleted');
                $this->table($header, $deleted);
            }

            if ($moved_count > 0) {
                $this->info('Moved');
                $this->table($header, $moved);
            }

            if ($changed_count > 0) {
                $this->info('Changed');
                $this->table($columns, $changed);
            }
        }

        $this->endGroup();
    }

    /**
     * Summary of getChanges
     *
     * @return array{districts: array, provinces: array, regencies: array, villages: array}
     */
    public function getDiffs()
    {
        $current = $this->libPath('database', 'nusa.sqlite');
        $updated = $this->libPath('database', "nusa.{$this->currentBranch()}.sqlite");
        $reports = [
            'added' => [],
            'changed' => [],
            'deleted' => [],
        ];

        $parser = new Parser(shell_exec("sqldiff --primarykey {$current} {$updated}"));

        foreach ($parser->statements as $statement) {
            if ($statement instanceof InsertStatement) {
                $reports['added'][$statement->into->dest->table][] = array_map(function ($value) {
                    if ($value === 'NULL') {
                        $value = null;
                    }

                    return $value;
                }, $statement->values[0]->values);

                continue;
            }

            if ($statement instanceof UpdateStatement) {
                [$field, $value] = explode('=', $statement->where[0]->expr, 2);

                $changes = [
                    $field => trim($value, "'"),
                ];

                foreach ($statement->set as $set) {
                    $changes[$set->column] = trim($set->value, "'");
                }

                $reports['changed'][$statement->tables[0]->expr][] = $changes;

                continue;
            }

            if ($statement instanceof DeleteStatement) {
                [$field, $value] = explode('=', $statement->where[0]->expr, 2);

                $reports['deleted'][$statement->from[0]->table][] = [
                    $field => trim($value, "'"),
                ];

                continue;
            }
        }

        $output = [
            'provinces' => [],
            'regencies' => [],
            'districts' => [],
            'villages' => [],
        ];

        foreach ($reports as $state => $report) {
            foreach ($report as $table => $statement) {
                $output[$table][$state] = $statement;
            }
        }

        return $output;
    }
}
