<?php

namespace Creasi\Tests\Features;

use Creasi\Tests\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

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

    /**
     * Assert that the response has a 200 "OK" status code and given JSON structure.
     */
    protected function assertSingleResponse(TestResponse $response, array $fields, array $with = []): TestResponse
    {
        $fields = \array_merge($fields, $with);

        return $response->assertOk()->assertJsonStructure([
            'data' => \array_filter($fields),
            'meta' => [],
        ]);
    }

    /**
     * Assert that the response has a 200 "OK" status code and given JSON structure.
     */
    protected function assertCollectionResponse(TestResponse $response, array $fields): TestResponse
    {
        // $fields = \array_merge($fields, $with);

        return $response->assertOk()->assertJsonStructure([
            'data' => [\array_filter($fields)],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'],
        ]);
    }
}
