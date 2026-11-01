<?php

declare(strict_types=1);

namespace Creasi\Tests\Fixture;

use Workbench\App\Support\GitHelper;

final class GitHelperFixture
{
    use GitHelper;

    public function branch(): string
    {
        return $this->currentBranch();
    }
}
