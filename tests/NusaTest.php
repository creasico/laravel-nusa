<?php

declare(strict_types=1);

namespace Creasi\Tests;

use PHPUnit\Framework\Attributes\Test;

class NusaTest extends TestCase
{
    #[Test]
    public function it_should_be_true()
    {
        if (! env('GIT_BRANCH')) {
            $this->artisan('nusa:sync', [
                'dbname' => 'cahyadsn_wilayah',
                '--user' => 'root',
                '--pass' => 'secret',
            ]);
        }

        $this->assertTrue(true);
    }
}
