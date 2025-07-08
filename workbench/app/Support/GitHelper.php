<?php

declare(strict_types=1);

namespace Workbench\App\Support;

trait GitHelper
{
    private function currentBranch(): string
    {
        $branch = env('GIT_BRANCH');

        if (! $branch) {
            $branch = trim(shell_exec('git rev-parse --abbrev-ref HEAD'));
        }

        return (string) str(str_replace('/', '_', $branch))->slug();
    }
}
