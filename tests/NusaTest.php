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
                'dbname' => env('DB_NAME', 'cahyadsn_wilayah'),
                '--host' => env('DB_HOST', '127.0.0.1'),
                '--user' => env('DB_USER', 'root'),
                '--pass' => env('DB_PASS', 'secret'),
            ]);
        }

        $this->assertTrue(true);
    }
}
