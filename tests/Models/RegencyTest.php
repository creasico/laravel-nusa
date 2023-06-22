<?php

declare(strict_types=1);

namespace Creasi\Tests\Models;

use Creasi\Nusa\Models\Regency;
use Creasi\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('model')]
class RegencyTest extends TestCase
{
    #[Test]
    public function it_should_be_true()
    {
        $this->assertTrue(\class_exists(Regency::class));
    }
}
