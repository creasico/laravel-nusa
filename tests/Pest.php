<?php

declare(strict_types=1);

use Creasi\Tests\Feature\TestCase as FeatureTestCase;
use Creasi\Tests\TestCase;

pest()->extend(TestCase::class)->in('Unit', 'Workbench');
pest()->extend(FeatureTestCase::class)->in('Feature');

/**
 * Set an environment variable for the current test process.
 */
function set_test_env(string $key, ?string $value): void
{
    if ($value === null) {
        putenv($key);
        unset($_ENV[$key], $_SERVER[$key]);

        return;
    }

    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}
