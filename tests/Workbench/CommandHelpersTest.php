<?php

declare(strict_types=1);

use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;
use Creasi\Nusa\Models\Village;
use Creasi\Tests\Fixture\CommandHelpersFixture;
use Creasi\Tests\TestCase;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Build a fixture command using the CommandHelpers trait.
 */
function helperCommand(): CommandHelpersFixture
{
    return new CommandHelpersFixture;
}

it('resolves the library path', function () {
    /** @var TestCase $this */
    $root = \dirname(__DIR__, 2);

    expect(helperCommand()->lib('workbench')->toString())
        ->toBe($root.\DIRECTORY_SEPARATOR.'workbench');
});

it('resolves models by table name', function (string $table, string $model) {
    /** @var TestCase $this */
    expect(helperCommand()->query($table)->getModel())->toBeInstanceOf($model);
})->with([
    'provinces' => ['provinces', Province::class],
    'regencies' => ['regencies', Regency::class],
    'districts' => ['districts', District::class],
    'villages' => ['villages', Village::class],
]);

it('detects whether it is running in ci', function () {
    /** @var TestCase $this */
    $previous = getenv('CI');

    try {
        set_test_env('CI', null);
        expect(helperCommand()->inCI())->toBeFalse();

        set_test_env('CI', 'true');
        expect(helperCommand()->inCI())->toBeTrue();
    } finally {
        set_test_env('CI', $previous === false ? null : $previous);
    }
});

it('writes local group output', function () {
    $previous = getenv('CI');
    $command = new CommandHelpersFixture;
    $output = new BufferedOutput;
    $command->setOutput(new OutputStyle(new ArrayInput([]), $output));

    try {
        set_test_env('CI', null);
        $command->startGroup('Title');
        $command->stopGroup();

        expect($output->fetch())->toBe("Title\n\n");
    } finally {
        set_test_env('CI', $previous === false ? null : $previous);
    }
});

it('writes ci group output', function () {
    $previous = getenv('CI');
    $command = new CommandHelpersFixture;
    $output = new BufferedOutput;
    $command->setOutput(new OutputStyle(new ArrayInput([]), $output));

    try {
        set_test_env('CI', 'true');
        $command->startGroup('Title');
        $command->stopGroup();

        expect($output->fetch())->toBe("::group::Title\n::endgroup::\n");
    } finally {
        set_test_env('CI', $previous === false ? null : $previous);
    }
});

it('rejects unknown table names', function () {
    helperCommand()->query('unknown');
})->throws(UnhandledMatchError::class);

it('writes ci output to the github output file', function () {
    /** @var TestCase $this */
    $previousCi = getenv('CI');
    $previousOutput = getenv('GITHUB_OUTPUT');
    $file = tempnam(sys_get_temp_dir(), 'nusa-ci-output-');

    try {
        set_test_env('CI', 'true');
        set_test_env('GITHUB_OUTPUT', $file);

        helperCommand()->writeCiOutput('has-changes=1');

        expect(file_get_contents($file))->toBe('has-changes=1'.\PHP_EOL);
    } finally {
        set_test_env('CI', $previousCi === false ? null : $previousCi);
        set_test_env('GITHUB_OUTPUT', $previousOutput === false ? null : $previousOutput);
        @unlink($file);
    }
});
