<?php

declare(strict_types=1);

namespace Workbench\App\Support;

use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Statement;

trait SqlHelper
{
    /**
     * Statement types from upstream dumps we are willing to execute.
     *
     * Everything else (e.g. `DELETE`, `UPDATE`, `SET`, `START TRANSACTION`) is
     * reported and skipped, so a dump can't wipe data or leak connection state.
     */
    private const SAFE_VERBS = ['INSERT', 'CREATE', 'ALTER', 'DROP'];

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

    /**
     * Split a dump into individual statements without parsing them into an AST.
     *
     * Semicolons inside string literals and comments are left untouched, since
     * upstream dumps carry `;` in headers, names, and postal code comments.
     *
     * @return \Generator<int, string>
     */
    private function splitStatements(string $sql): \Generator
    {
        $sql = ltrim($sql, "\xEF\xBB\xBF"); // upstream dumps start with a BOM
        $length = \strlen($sql);
        $start = $offset = 0;

        while ($offset < $length) {
            $offset += strcspn($sql, "'\"`#-/*;", $offset);

            if ($offset >= $length) {
                break;
            }

            $char = $sql[$offset];
            $position = $offset;

            // keep quoted literals (and escaped or doubled quotes) intact
            if ($char === "'" || $char === '"' || $char === '`') {
                $mask = $char === '`' ? '`' : $char.'\\';

                for ($offset++; $offset < $length;) {
                    $offset += strcspn($sql, $mask, $offset);

                    if ($offset >= $length) {
                        break;
                    }

                    if ($sql[$offset] === '\\') {
                        $offset += 2;

                        continue;
                    }

                    if (($sql[$offset + 1] ?? '') === $char) {
                        $offset += 2;

                        continue;
                    }

                    $offset++;

                    break;
                }

                continue;
            }

            // shortcut: no `DELIMITER` support, upstream dumps contain no
            // triggers or procedures, only plain `;` terminated statements.
            if ($char === '#' || ($char === '-' && ($sql[$offset + 1] ?? '') === '-')) {
                $newline = strpos($sql, "\n", $offset);
                $end = $newline === false ? $length : $newline;

                if (trim(substr($sql, $start, $position - $start)) === '') {
                    $start = $end;
                }

                $offset = $end;

                continue;
            }

            if ($char === '/' && ($sql[$offset + 1] ?? '') === '*') {
                $end = strpos($sql, '*/', $position + 2);
                $end = $end === false ? $length : $end + 2;

                if (trim(substr($sql, $start, $position - $start)) === '') {
                    $start = $end;
                }

                $offset = $end;

                continue;
            }

            if ($char === ';') {
                $statement = trim(substr($sql, $start, $offset - $start));
                $start = ++$offset;

                if ($statement !== '') {
                    yield $statement;
                }

                continue;
            }

            $offset++; // a lone `-`, `/`, or `#` that starts no comment
        }

        if (($statement = trim(substr($sql, $start))) !== '') {
            yield $statement;
        }
    }

    private function statementVerb(string $statement): string
    {
        preg_match('/^[A-Za-z]+/', $statement, $matches);

        return strtoupper($matches[0] ?? 'UNKNOWN');
    }

    private function isSafeStatement(string $statement, string $verb): bool
    {
        if (! \in_array($verb, self::SAFE_VERBS, true)) {
            return false;
        }

        // shortcut: upstream only legitimately drops a table right before it
        // re-creates it, anything else (e.g. `DROP TABLE wilayah`) is refused.
        return $verb !== 'DROP' || preg_match('/^DROP\s+TABLE\s+IF\s+EXISTS\b/i', $statement) === 1;
    }
}
