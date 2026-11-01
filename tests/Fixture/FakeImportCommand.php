<?php

declare(strict_types=1);

namespace Creasi\Tests\Fixture;

use Workbench\App\Console\ImportCommand;

final class FakeImportCommand extends ImportCommand
{
    public array $scanned = [];

    public array $fetched = [];

    public bool $refreshed = false;

    public array $calls = [];

    protected function scanSqlFiles(array $paths = []): \Generator
    {
        yield from $this->scanned;
    }

    protected function fetchAll(): array
    {
        return $this->fetched;
    }

    protected function refreshDatabase(): void
    {
        $this->refreshed = true;
    }

    public function call($command, array $arguments = [], $outputBuffer = null): int
    {
        $this->calls[] = $command;

        return 0;
    }
}
