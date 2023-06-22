<?php

declare(strict_types=1);

namespace Creasi\Nusa\Console;

use Illuminate\Console\Command;

class SyncCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'nusa:sync';

    /**
     * @var string
     */
    protected $description = 'Sync database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        //

        return 0;
    }
}
