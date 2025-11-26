<?php

declare(strict_types=1);

namespace Workbench\App\Support;

trait GitHelper
{
    private function currentBranch(): string
    {
        $branch = env('GIT_BRANCH', fn () => trim(shell_exec('git rev-parse --abbrev-ref HEAD')));

        return (string) str($branch)->slug('_', dictionary: ['/' => '_']);
    }
}
