<?php

declare(strict_types=1);

namespace Creasi\Tests\Fixture;

use Illuminate\Console\Command;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Stringable;
use Workbench\App\Console\CommandHelpers;

final class CommandHelpersFixture extends Command
{
    use CommandHelpers;

    protected $signature = 'nusa:test-command-helpers';

    protected $description = 'Fixture command for the CommandHelpers trait';

    public function handle(): int
    {
        return 0;
    }

    /**
     * Resolve a path from the repository root.
     */
    public function lib(string ...$paths): Stringable
    {
        return $this->libPath(...$paths);
    }

    /**
     * Resolve a model query by table name.
     */
    public function query(string $table): Builder
    {
        return $this->model($table);
    }

    /**
     * Determine whether the command runs in CI.
     */
    public function inCI(): bool
    {
        return $this->runningInCI();
    }

    /**
     * Write a value to the GitHub output file.
     */
    public function writeCiOutput(string $output): void
    {
        $this->ciOutput($output);
    }

    /**
     * Start a helper output group.
     */
    public function startGroup(string $title): void
    {
        $this->group($title);
    }

    /**
     * End a helper output group.
     */
    public function stopGroup(): void
    {
        $this->endGroup();
    }
}
