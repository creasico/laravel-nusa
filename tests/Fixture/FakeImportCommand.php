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

    /**
     * Yield the configured SQL fixtures.
     */
    protected function scanSqlFiles(array $paths = []): \Generator
    {
        yield from $this->scanned;
    }

    /**
     * Return the configured upstream rows.
     */
    protected function fetchAll(): array
    {
        return $this->fetched;
    }

    /**
     * Record that the database was refreshed.
     */
    protected function refreshDatabase(): void
    {
        $this->refreshed = true;
    }

    /**
     * Record delegated command calls.
     */
    public function call($command, array $arguments = [], $outputBuffer = null): int
    {
        $this->calls[] = $command;

        return 0;
    }
}
