<?php

declare(strict_types=1);

namespace Creasi\Tests\Fixture;

use Workbench\App\Support\SqlHelper;

final class SqlHelperFixture
{
    use SqlHelper;

    /**
     * Split a SQL dump into statements.
     */
    public function split(string $sql): array
    {
        return iterator_to_array($this->splitStatements($sql));
    }

    /**
     * Parse a SQL dump into statement classes.
     */
    public function verbs(string $sql): array
    {
        $verbs = [];

        foreach ($this->parseQuery($sql) as $statement) {
            $verbs[] = $statement::class;
        }

        return $verbs;
    }

    /**
     * Detect the verb of a SQL statement.
     */
    public function verb(string $statement): string
    {
        return $this->statementVerb($statement);
    }

    /**
     * Determine whether a SQL statement is safe to execute.
     */
    public function safe(string $statement): bool
    {
        return $this->isSafeStatement($statement, $this->statementVerb($statement));
    }
}
