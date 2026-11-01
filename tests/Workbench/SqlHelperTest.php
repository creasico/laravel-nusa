<?php

declare(strict_types=1);

use Creasi\Tests\Fixture\SqlHelperFixture;
use Creasi\Tests\TestCase;
use PhpMyAdmin\SqlParser\Statements\SelectStatement;

/**
 * Build a fixture using the SqlHelper trait.
 */
function sql(): SqlHelperFixture
{
    return new SqlHelperFixture;
}

it('splits statements on semicolons', function () {
    /** @var TestCase $this */
    expect(sql()->split('INSERT INTO t VALUES (1); SELECT 1;'))
        ->toBe(['INSERT INTO t VALUES (1)', 'SELECT 1']);
});

it('keeps semicolons inside quoted literals', function () {
    /** @var TestCase $this */
    expect(sql()->split("INSERT INTO t VALUES ('a;b');"))
        ->toBe(["INSERT INTO t VALUES ('a;b')"]);
});

it('keeps escaped and doubled quotes inside literals', function () {
    /** @var TestCase $this */
    expect(sql()->split("INSERT INTO t VALUES ('it\\'s; fine', 'it''s; fine');"))
        ->toBe(["INSERT INTO t VALUES ('it\\'s; fine', 'it''s; fine')"]);
});

it('keeps semicolons inside backtick identifiers', function () {
    /** @var TestCase $this */
    expect(sql()->split('SELECT `weird;name` FROM t;'))
        ->toBe(['SELECT `weird;name` FROM t']);
});

it('strips line and block comments', function () {
    /** @var TestCase $this */
    $sql = <<<'SQL'
    -- a comment; with semicolon
    # another comment;
    /* block; comment */
    SELECT 1;
    SQL;

    expect(sql()->split($sql))->toBe(['SELECT 1']);
});

it('strips a leading byte order mark', function () {
    /** @var TestCase $this */
    expect(sql()->split("\xEF\xBB\xBFSELECT 1;"))->toBe(['SELECT 1']);
});

it('returns no statements for empty input', function () {
    /** @var TestCase $this */
    expect(sql()->split('  '))->toBe([]);
});

it('handles an unterminated quoted literal', function () {
    /** @var TestCase $this */
    expect(sql()->split("SELECT 'abc"))->toBe(["SELECT 'abc"]);
});

it('keeps lone operators', function () {
    /** @var TestCase $this */
    expect(sql()->split('SELECT 1 - 2; SELECT 3 / 4;'))
        ->toBe(['SELECT 1 - 2', 'SELECT 3 / 4']);
});

it('yields a trailing statement without a semicolon', function () {
    /** @var TestCase $this */
    expect(sql()->split('SELECT 1'))->toBe(['SELECT 1']);
});

it('detects statement verbs', function () {
    /** @var TestCase $this */
    expect(sql()->verb('  insert into t values (1)'))->toBe('UNKNOWN')
        ->and(sql()->verb('insert into t values (1)'))->toBe('INSERT');
});

it('only allows safe statements', function (string $statement, bool $safe) {
    /** @var TestCase $this */
    expect(sql()->safe($statement))->toBe($safe);
})->with([
    'insert' => ['INSERT INTO t VALUES (1)', true],
    'create' => ['CREATE TABLE t (id int)', true],
    'alter' => ['ALTER TABLE t ADD x int', true],
    'safe drop' => ['DROP TABLE IF EXISTS t', true],
    'unsafe drop' => ['DROP TABLE t', false],
    'delete' => ['DELETE FROM t', false],
    'update' => ['UPDATE t SET x = 1', false],
    'set' => ['SET x = 1', false],
    'transaction' => ['START TRANSACTION', false],
]);

it('parses statements into an ast', function () {
    /** @var TestCase $this */
    expect(sql()->verbs('SELECT 1; SELECT 2;'))->toBe([
        SelectStatement::class,
        SelectStatement::class,
    ]);
});
