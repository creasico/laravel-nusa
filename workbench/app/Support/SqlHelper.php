<?php

declare(strict_types=1);

namespace Workbench\App\Support;

use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Statement;

trait SqlHelper
{
    /**
     * @return \Generator<Statement>
     */
    private function parse(string $query): \Generator
    {
        $parser = new Parser($query);

        foreach ($parser->statements as $statement) {
            yield $statement;
        }
    }
}
