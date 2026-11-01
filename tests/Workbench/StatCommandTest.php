<?php

declare(strict_types=1);

use Creasi\Tests\Fixture\FakeStatCommand;
use Illuminate\Console\OutputStyle;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Workbench\App\Console\StatCommand;

function statCurrentDatabase(array $provinces): array
{
    $root = \dirname(__DIR__, 2);
    $database = "{$root}/workbench/storage/stat-".Str::lower(Str::random(8)).'.sqlite';
    $previous = config('database.connections.current');

    touch($database);
    config(['database.connections.current' => ['driver' => 'sqlite', 'database' => $database]]);
    DB::purge('current');

    Schema::connection('current')->create('provinces', function (Blueprint $table) {
        $table->string('code')->primary();
        $table->string('name');
        $table->double('latitude')->nullable();
        $table->double('longitude')->nullable();
    });

    DB::connection('current')->table('provinces')->insert($provinces);

    return compact('database', 'previous');
}

function cleanupStatCurrentDatabase(array $fixture): void
{
    config(['database.connections.current' => $fixture['previous']]);
    DB::purge('current');
    @unlink($fixture['database']);
}

it('fails when the distribution database is missing', function () {
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

it('parses insert, update and delete statements', function () {
    $branch = 'test-stat-'.Str::lower(Str::random(8));
    $path = \dirname(__DIR__, 2)."/database/nusa.{$branch}.sqlite";
    touch($path);

    $command = new FakeStatCommand;
    $command->diffOutput = <<<'SQL'
        INSERT INTO provinces (code, name, latitude, longitude) VALUES ('33', 'Jawa Tengah', 1.0, 2.0);
        UPDATE provinces SET name = 'Jawa Tengah Baru' WHERE code = '33';
        DELETE FROM provinces WHERE code = '34';
        SQL;

    try {
        $diffs = $command->getDiffs($branch);

        expect($diffs['provinces']['added'])->toHaveCount(1)
            ->and($diffs['provinces']['added'][0])->toContain('33', 'Jawa Tengah')
            ->and($diffs['provinces']['changed'][0])->toMatchArray([
                'code' => '33',
                'name' => 'Jawa Tengah Baru',
            ])
            ->and($diffs['provinces']['deleted'][0])->toMatchArray(['code' => '34']);
    } finally {
        @unlink($path);
    }
});

it('renders a markdown report', function () {
    $root = \dirname(__DIR__, 2);
    $database = "{$root}/workbench/storage/stat-".Str::lower(Str::random(8)).'.sqlite';
    $report = "{$root}/workbench/storage/stat-report-".Str::lower(Str::random(8)).'.md';
    $previous = config('database.connections.current');

    try {
        touch($database);
        config(['database.connections.current' => ['driver' => 'sqlite', 'database' => $database]]);
        DB::purge('current');

        Schema::connection('current')->create('provinces', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('name');
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
        });
        Schema::connection('current')->create('villages', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('name');
            $table->string('postal_code')->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
        });

        DB::connection('current')->table('provinces')->insert([
            'code' => '33',
            'name' => 'Jawa Tengah',
            'latitude' => 1.0,
            'longitude' => 2.0,
        ]);
        DB::connection('current')->table('villages')->insert([
            'code' => '33.75.03.1006',
            'name' => 'Padukuhan Kraton',
            'postal_code' => '50000',
            'latitude' => 1.0,
            'longitude' => 2.0,
        ]);

        $command = new FakeStatCommand;
        $command->setLaravel($this->app);
        $command->diffs = [
            'provinces' => [
                'added' => [],
                'moved' => [],
                'changed' => [['code' => '33', 'name' => 'Jawa Tengah Baru']],
                'deleted' => [],
            ],
            'regencies' => ['added' => [], 'moved' => [], 'changed' => [], 'deleted' => []],
            'districts' => ['added' => [], 'moved' => [], 'changed' => [], 'deleted' => []],
            'villages' => [
                'added' => [],
                'moved' => [],
                'changed' => [['code' => '33.75.03.1006', 'postal_code' => '51111']],
                'deleted' => [],
            ],
        ];

        $exit = $command->run(new ArrayInput(['--output' => $report]), new BufferedOutput);

        expect($exit)->toBe(0)
            ->and(file_exists($report))->toBeTrue()
            ->and(file_get_contents($report))->toContain('### 1 province and 1 village got updated')
            ->and(file_get_contents($report))->toContain('Jawa Tengah Baru')
            ->and(file_get_contents($report))->toContain('51111');
    } finally {
        config(['database.connections.current' => $previous]);
        DB::purge('current');
        @unlink($database);
        @unlink($report);
    }
});

it('writes ci output for changed tables', function () {
    $file = tempnam(sys_get_temp_dir(), 'nusa-stat-output-');
    $previousCi = getenv('CI');
    $previousOutput = getenv('GITHUB_OUTPUT');

    try {
        set_test_env('CI', 'true');
        set_test_env('GITHUB_OUTPUT', $file);

        $command = new FakeStatCommand;
        $command->setLaravel($this->app);
        $command->diffs = [
            'provinces' => ['added' => [], 'moved' => [], 'changed' => [], 'deleted' => []],
            'regencies' => ['added' => [], 'moved' => [], 'changed' => [], 'deleted' => []],
            'districts' => ['added' => [], 'moved' => [], 'changed' => [], 'deleted' => []],
            'villages' => ['added' => [], 'moved' => [], 'changed' => [], 'deleted' => []],
        ];

        $command->run(new ArrayInput([]), new BufferedOutput);

        expect(file_get_contents($file))->toBe('has-changes=0'.\PHP_EOL);
    } finally {
        set_test_env('CI', $previousCi === false ? null : $previousCi);
        set_test_env('GITHUB_OUTPUT', $previousOutput === false ? null : $previousOutput);
        @unlink($file);
    }
});

it('truncates long markdown tables', function () {
    $rows = array_map(fn (int $i) => [(string) $i, "Row {$i}"], range(1, 101));

    $command = new StatCommand;
    $method = new ReflectionMethod($command, 'renderMarkdown');

    $markdown = $method->invoke($command, [
        'provinces' => [
            'columns' => ['code', 'name'],
            'added' => $rows,
            'moved' => [],
            'changed' => [],
            'deleted' => [],
        ],
    ]);

    expect($markdown)
        ->toContain('### 101 provinces got updated')
        ->toContain('_and 1 more_');
});

it('renders a changed field with an arrow', function () {
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

    expect($markdown)->toContain('→ **Jawa Tengah Baru**');
});

it('detects moved rows and renders every output section', function () {
    $fixture = statCurrentDatabase([
        ['code' => '33', 'name' => 'Changed Province', 'latitude' => 1.0, 'longitude' => 2.0],
        ['code' => '34', 'name' => 'Moved Province', 'latitude' => 1.0, 'longitude' => 2.0],
        ['code' => '37', 'name' => 'Deleted Province', 'latitude' => 1.0, 'longitude' => 2.0],
    ]);
    $report = \dirname(__DIR__, 2).'/workbench/storage/stat-report-'.Str::lower(Str::random(8)).'.md';

    try {
        $command = new FakeStatCommand;
        $command->setLaravel($this->app);
        $command->diffs = [
            'provinces' => [
                'added' => [['35', 'Moved Province'], ['36', 'Added Province']],
                'moved' => [],
                'changed' => [['code' => '33', 'name' => 'Changed Province 2']],
                'deleted' => [['code' => '34'], ['code' => '37']],
            ],
            'regencies' => ['added' => [], 'moved' => [], 'changed' => [], 'deleted' => []],
            'districts' => ['added' => [], 'moved' => [], 'changed' => [], 'deleted' => []],
            'villages' => ['added' => [], 'moved' => [], 'changed' => [], 'deleted' => []],
        ];

        $output = new BufferedOutput;
        $exit = $command->run(new ArrayInput(['--output' => $report]), $output);

        $text = $output->fetch();
        $markdown = file_get_contents($report);

        expect($exit)->toBe(0)
            ->and($text)->toContain('Added')
            ->and($text)->toContain('Deleted')
            ->and($text)->toContain('Moved')
            ->and($text)->toContain('Changed')
            ->and($markdown)->toContain('| moved |')
            ->and($markdown)->toContain('| deleted |')
            ->and($markdown)->toContain('34')
            ->and($markdown)->toContain('35');
    } finally {
        cleanupStatCurrentDatabase($fixture);
        @unlink($report);
    }
});

it('reports a sqldiff failure', function () {
    if (! shell_exec('command -v sqldiff')) {
        $this->markTestSkipped('sqldiff is not installed.');
    }

    $branch = 'test-stat-diff-'.Str::lower(Str::random(8));
    $path = \dirname(__DIR__, 2)."/database/nusa.{$branch}.sqlite";
    file_put_contents($path, 'not a database');

    $command = new StatCommand;
    $output = new BufferedOutput;
    $command->setOutput(new OutputStyle(new ArrayInput([]), $output));

    try {
        expect($command->getDiffs($branch))->toBeNull()
            ->and($output->fetch())->toContain('sqldiff failed');
    } finally {
        @unlink($path);
    }
});

it('reports a failed report write', function () {
    $directory = \dirname(__DIR__, 2).'/workbench/storage';
    $command = new FakeStatCommand;
    $command->setLaravel($this->app);
    $command->diffs = [
        'provinces' => ['added' => [], 'moved' => [], 'changed' => [], 'deleted' => []],
        'regencies' => ['added' => [], 'moved' => [], 'changed' => [], 'deleted' => []],
        'districts' => ['added' => [], 'moved' => [], 'changed' => [], 'deleted' => []],
        'villages' => ['added' => [], 'moved' => [], 'changed' => [], 'deleted' => []],
    ];

    $output = new BufferedOutput;

    set_error_handler(fn () => true);

    try {
        $command->run(new ArrayInput(['--output' => $directory]), $output);
    } finally {
        restore_error_handler();
    }

    expect($output->fetch())->toContain('Cannot write report');
});

it('handles empty markdown and empty values', function () {
    $command = new StatCommand;
    $renderMarkdown = new ReflectionMethod($command, 'renderMarkdown');
    $markdownValue = new ReflectionMethod($command, 'markdownValue');

    expect($renderMarkdown->invoke($command, []))->toBe('')
        ->and($markdownValue->invoke($command, null))->toBe('—')
        ->and($markdownValue->invoke($command, ''))->toBe('—');
});

it('sanitizes sql values', function (string $value, ?string $expected) {
    $command = new StatCommand;
    $method = new ReflectionMethod($command, 'sanitizeValue');

    expect($method->invoke($command, $value))->toBe($expected);
})->with([
    'quoted value' => ["'33'", '33'],
    'null value' => ['NULL', null],
    'plain value' => ['Jawa Tengah', 'Jawa Tengah'],
]);
