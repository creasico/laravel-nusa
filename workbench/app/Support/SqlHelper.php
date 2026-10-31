<?php

declare(strict_types=1);

namespace Workbench\App\Support;

use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Statement;

trait SqlHelper
{
    /**
     * @return \Generator<int, Statement>
     */
    private function parseQuery(string $query): \Generator
    {
        $parser = new Parser($query);

        foreach ($parser->statements as $i => $statement) {
            yield $i => $statement;
        }

        $parser = null;
    }
}
