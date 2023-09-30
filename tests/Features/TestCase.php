<?php

namespace Creasi\Tests\Features;

use Creasi\Tests\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $path = '';

    protected function path(string $path = null, array $query = []): string
    {
        $path = $this->path.'/'.ltrim($path);

        if (! empty($query)) {
            $path .= '?'.http_build_query($query);
        }

        return $path;
    }

    abstract public static function availableQueries(): array;

    abstract public static function invalidCodes(): array;
}
