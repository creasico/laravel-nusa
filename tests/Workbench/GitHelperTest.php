<?php

declare(strict_types=1);

use Creasi\Tests\Fixture\GitHelperFixture;
use Creasi\Tests\TestCase;

it('normalizes the current branch name', function () {
    /** @var TestCase $this */
    $previous = getenv('GIT_BRANCH');

    try {
        set_test_env('GIT_BRANCH', 'feat/pest-refactor');

        expect((new GitHelperFixture)->branch())->toBe('feat_pest-refactor');
    } finally {
        set_test_env('GIT_BRANCH', $previous === false ? null : $previous);
    }
});
