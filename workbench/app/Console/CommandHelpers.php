<?php

namespace Workbench\App\Console;

/**
 * @mixin \Illuminate\Console\Command
 */
trait CommandHelpers
{
    private bool $ciGroup = false;

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