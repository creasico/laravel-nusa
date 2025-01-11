<?php

namespace Workbench\App\Console;

use Illuminate\Support\Stringable;

/**
 * @mixin \Illuminate\Console\Command
 */
trait CommandHelpers
{
    private bool $ciGroup = false;

    private function libPath(string ...$paths): Stringable
    {
        $path = \dirname(__DIR__).'/../..';

        if (! empty($paths)) {
            $path .= '/'.implode(DIRECTORY_SEPARATOR, $paths);
        }

        return str(\realpath($path) ?: null);
    }

    private function group(string $title): void
    {
        if (env('CI') === null) {
            return;
        }

        $this->endGroup();

        $this->line('::group::'.$title);
        $this->ciGroup = true;
    }

    private function endGroup(): void
    {
        if (env('CI') === null) {
            return;
        }

        if ($this->ciGroup) {
            $this->line('::endgroup::');
            $this->ciGroup = false;
        }
    }
}
