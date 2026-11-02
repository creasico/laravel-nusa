<?php

declare(strict_types=1);

namespace Workbench\App\Console;

use Creasi\Nusa\Models;
use Creasi\Nusa\Models\Model;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Stringable;

/**
 * @mixin Command
 */
trait CommandHelpers
{
    private bool $groupStarted = false;

    private function libPath(string ...$paths): Stringable
    {
        return str(\dirname(__DIR__, 3).\DIRECTORY_SEPARATOR.implode(\DIRECTORY_SEPARATOR, $paths));
    }

    private function databasePath(string ...$paths): Stringable
    {
        $directory = config('creasi.nusa.database_dir', fn () => $this->libPath('database')->toString());

        return str($directory.\DIRECTORY_SEPARATOR.implode(\DIRECTORY_SEPARATOR, $paths));
    }

    private function group(string $title): void
    {
        $this->endGroup();

        $this->groupStarted = true;

        if ($this->runningInCI()) {
            $this->line('::group::'.$title);

            return;
        }

        $this->line($title);
    }

    private function endGroup(): void
    {
        if (! $this->groupStarted) {
            return;
        }

        if ($ci = $this->runningInCI()) {
            $this->line('::endgroup::');
        }

        $this->groupStarted = false;

        if (! $ci) {
            $this->line('');
        }
    }

    private function ciOutput(string $output): void
    {
        if ($this->runningInCI()) {
            file_put_contents((string) getenv('GITHUB_OUTPUT'), $output.\PHP_EOL, \FILE_APPEND);
        }
    }

    private function runningInCI(): bool
    {
        return env('CI') !== null;
    }

    /**
     * @return Builder<Model>
     */
    private function model(string $table): Builder
    {
        $query = match ($table) {
            'provinces' => Models\Province::query(),
            'regencies' => Models\Regency::query(),
            'districts' => Models\District::query(),
            'villages' => Models\Village::query(),
            default => throw new \InvalidArgumentException("Unknown table: {$table}"),
        };

        /** @var Builder<Model> $result */
        $result = $query;

        return $result;
    }
}
