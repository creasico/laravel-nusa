<?php

declare(strict_types=1);

namespace Workbench\App\Support;

use Illuminate\Console\View\Components\TwoColumnDetail;

final class Timer
{
    private float $startTime;

    public function __construct(
        private string $caption,
        private TwoColumnDetail $column,
    ) {
        $this->startTime = microtime(true);
    }

    public function stop(): void
    {
        $runTime = number_format((microtime(true) - $this->startTime) * 1000);

        $this->column->render(
            "  {$this->caption}",
            "<fg=gray>{$runTime}ms</> <fg=green;options=bold>DONE</>"
        );
    }
}
