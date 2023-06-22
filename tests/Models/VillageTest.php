<?php

declare(strict_types=1);

namespace Creasi\Tests\Models;

use Creasi\Nusa\Models\Village;
use Creasi\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('model')]
class VillageTest extends TestCase
{
    #[Test]
    public function it_should_be_true()
    {
        $this->assertTrue(\class_exists(Village::class));
    }
}
