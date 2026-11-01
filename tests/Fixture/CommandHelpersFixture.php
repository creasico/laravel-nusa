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

    public function lib(string ...$paths): Stringable
    {
        return $this->libPath(...$paths);
    }

    public function query(string $table): Builder
    {
        return $this->model($table);
    }

    public function inCI(): bool
    {
        return $this->runningInCI();
    }

    public function writeCiOutput(string $output): void
    {
        $this->ciOutput($output);
    }
}
