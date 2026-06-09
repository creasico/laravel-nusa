<?php

declare(strict_types=1);

namespace Creasi\Nusa\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
interface HasSubdivision
{
    /**
     * @return Collection<int, \Creasi\Nusa\Models\Model>
     */
    public function subdivisions(): Collection;
}
