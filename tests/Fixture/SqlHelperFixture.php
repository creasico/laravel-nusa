<?php

declare(strict_types=1);

namespace Creasi\Tests\Fixture;

use Workbench\App\Support\SqlHelper;

final class SqlHelperFixture
{
    use SqlHelper;

    public function split(string $sql): array
    {
        return iterator_to_array($this->splitStatements($sql));
    }

    public function verbs(string $sql): array
    {
        $verbs = [];

        foreach ($this->parseQuery($sql) as $statement) {
            $verbs[] = $statement::class;
        }

        return $verbs;
    }

    public function verb(string $statement): string
    {
        return $this->statementVerb($statement);
    }

    public function safe(string $statement): bool
    {
        return $this->isSafeStatement($statement, $this->statementVerb($statement));
    }
}
