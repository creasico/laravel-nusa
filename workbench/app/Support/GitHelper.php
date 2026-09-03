<?php

declare(strict_types=1);

namespace Workbench\App\Support;

trait GitHelper
{
    private function currentBranch(): string
    {
        $branch = env('GIT_BRANCH', function () {
            if ($branch = shell_exec('git rev-parse --abbrev-ref HEAD')) {
                return $branch;
            }

            return 'main';
        });

        return (string) str($branch)->slug('_', dictionary: ['/' => '_']);
    }
}
