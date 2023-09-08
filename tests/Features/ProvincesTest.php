<?php

namespace Creasi\Tests\Features;

use Creasi\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('api')]
class ProvincesTest extends TestCase
{
    #[Test]
    public function it_should_be_true()
    {
        $response = $this->getJson('nusa/provinces');

        $response->assertOk();
    }
}
