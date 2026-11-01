<?php

declare(strict_types=1);

use Creasi\Tests\TestCase;
use Illuminate\Support\Str;
use Workbench\App\Console\StatCommand;

it('fails when the distribution database is missing', function () {
    /** @var TestCase $this */
    $branch = 'test-stat-'.Str::lower(Str::random(8));
    $previous = getenv('GIT_BRANCH');

    try {
        set_test_env('GIT_BRANCH', $branch);

        $this->artisan('nusa:stat')
            ->expectsOutputToContain('Cannot open database file')
            ->assertExitCode(1);
    } finally {
        set_test_env('GIT_BRANCH', $previous === false ? null : $previous);
        @unlink(\dirname(__DIR__, 2)."/database/nusa.{$branch}.sqlite");
    }
});

it('renders a markdown report', function () {
    /** @var TestCase $this */
    $command = new StatCommand;
    $method = new ReflectionMethod($command, 'renderMarkdown');

    $markdown = $method->invoke($command, [
        'provinces' => [
            'columns' => ['code', 'name'],
            'added' => [],
            'moved' => [],
            'changed' => [
                ['code' => '33', 'name' => '<fg=yellow>→</> <fg=green>Jawa Tengah Baru</>'],
            ],
            'deleted' => [],
        ],
    ]);

    expect($markdown)
        ->toContain('### 1 province got updated')
        ->toContain('| state | code | name |')
        ->toContain('→ **Jawa Tengah Baru**');
});

it('sanitizes sql values', function (string $value, ?string $expected) {
    /** @var TestCase $this */
    $command = new StatCommand;
    $method = new ReflectionMethod($command, 'sanitizeValue');

    expect($method->invoke($command, $value))->toBe($expected);
})->with([
    'quoted value' => ["'33'", '33'],
    'null value' => ['NULL', null],
    'plain value' => ['Jawa Tengah', 'Jawa Tengah'],
]);
