<?php

declare(strict_types=1);

namespace Creasi\Tests\Feature;

use Creasi\Tests\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    protected string $path = '';

    protected function path(?string $path = null, array $query = []): string
    {
        $path = trim($this->path.'/'.ltrim($path ?? '', '/'), '/');

        if ($query !== []) {
            $path .= '?'.http_build_query($query);
        }

        return $path;
    }

    /**
     * Assert a 200 response containing a single resource.
     */
    protected function assertSingleResponse(TestResponse $response, array $fields, array $with = []): TestResponse
    {
        return $response->assertOk()->assertJsonStructure([
            'data' => array_values(array_filter([...$fields, ...$with])),
            'meta' => [],
        ]);
    }

    /**
     * Assert a 200 response containing a paginated collection.
     */
    protected function assertCollectionResponse(TestResponse $response, array $fields, array $with = []): TestResponse
    {
        // Relation loading on list endpoints is not wired into the paginator,
        // only the computed attributes are rendered straight from the query.
        $with = array_intersect($with, ['postal_codes', 'coordinates']);

        return $response->assertOk()->assertJsonStructure([
            'data' => [array_values(array_filter([...$fields, ...$with]))],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'],
        ]);
    }

    /**
     * Assert the paginator reported the expected total and a single default page.
     */
    protected function assertPaginatedTotal(TestResponse $response, int $total): TestResponse
    {
        $perPage = (int) $response->json('meta.per_page');

        return $response
            ->assertJsonPath('meta.total', $total)
            ->assertJsonCount(min($total, $perPage), 'data');
    }
}
