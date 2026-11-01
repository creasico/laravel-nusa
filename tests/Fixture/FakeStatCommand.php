<?php

declare(strict_types=1);

namespace Creasi\Tests\Fixture;

use Workbench\App\Console\StatCommand;

final class FakeStatCommand extends StatCommand
{
    public ?array $diffs = null;

    public ?string $diffOutput = null;

    /**
     * Use fixture diffs when provided.
     */
    public function getDiffs(string $branch): ?array
    {
        return $this->diffs ?? parent::getDiffs($branch);
    }

    /**
     * Return the configured raw diff output.
     */
    protected function runDiff(string $current, string $updated): ?string
    {
        return $this->diffOutput;
    }
}
