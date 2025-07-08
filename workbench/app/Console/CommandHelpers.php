<?php

declare(strict_types=1);

namespace Workbench\App\Console;

use Creasi\Nusa\Models;
use Illuminate\Support\Stringable;

/**
 * @mixin \Illuminate\Console\Command
 */
trait CommandHelpers
{
    private bool $groupStarted = false;

    private function libPath(string ...$paths): Stringable
    {
        $path = realpath(\dirname(__DIR__).'/../..');

        if (! empty($paths)) {
            $path .= DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $paths);
        }

        return str($path);
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

        if ($this->runningInCI()) {
            $this->line('::endgroup::');
        }

        $this->groupStarted = false;
        $this->line('');
    }

    private function runningInCI(): bool
    {
        return env('CI') !== null;
    }

    private function model(string $table)
    {
        return match ($table) {
            'provinces' => Models\Province::query(),
            'regencies' => Models\Regency::query(),
            'districts' => Models\District::query(),
            'villages' => Models\Village::query(),
        };
    }
}
