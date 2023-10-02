<?php

declare(strict_types=1);

namespace Creasi\Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('api')]
#[Group('models')]
#[Group('serviceProvider')]
class ServiceProviderTest extends TestCase
{
    #[Test]
    public function it_should_be_true(): void
    {
        if (! env('GIT_BRANCH')) {
            $this->artisan('nusa:sync');
        }

        $this->assertTrue(true);
    }
}
